<?php
namespace App\AdminModule\ImportManagerModule\Presenters;

use App\Model\Entity\Eshop\CategoryModel;
use App\Model\Entity\Eshop\CategoryProductModel;
use App\Model\Entity\Eshop\ProductModel;
use App\Model\Entity\Import\ImportPlacekModel;
use App\Model\ImportTools;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;

class HomepagePresenter extends BasePresenter
{

    const PLACEK_FILENAME = 'placekImport.xml';

    /**
     * @var ImportPlacekModel $importModel
     */
    public $importModel;

    /**
     * @var CategoryModel $categoryModel
     */
    public $categoryModel;

    /**
     * @var ProductModel $productModel
     */
    public $productModel;

    /**
     * @var CategoryProductModel $categoryProductModel
     */
    public $categoryProductModel;

    public function handleFormImport(Form $form)
    {

        $values = $form->getValues();

        $json = $this->context->parameters['webimages']['uploadDir'] . '/../xml/convertcsv.json';

        $rows = json_decode(file_get_contents($json));

        $counter = 0;
        $limit   = 3000;
        $offset  = 1;
        foreach ($rows as $row) {
            $counter++;
            if ($counter >= $limit * $offset AND $counter < $limit * ($offset + 1)) {
                $id_product = $this->productModel->productParamModel->getFluent('id_product')
                    ->where('code = %s', $row->FIELD1)->fetchSingle();

                if ($id_product) {

                    $arr = [
                        'name'             => $row->FIELD2,
                        'meta_title'       => $row->FIELD2,
                        'description'      => $row->FIELD4,
                        'meta_description' => $row->FIELD4,
                        'id_lang'          => 1
                    ];

                    $this->productModel->productLangModel->update($arr, ['id_lang' => 1, 'id_product' => $id_product]);
                }
            }
        }
        exit;
    }

    public function actionNewProducts()
    {
        $id_lang             = 1;
        $id_product_supplier = 1;
        $list                = $this->importModel->getFluent()->fetchPairs('Kod', 'Kod');

        $this->template->deleteProducts = $this->productModel->productParamModel->getFluent()->where('code NOT IN %l', $list)->fetchAll();

        $list2                       = $this->productModel->productParamModel->getFluent()->fetchPairs('code', 'code');
        $this->template->newProducts = $this->importModel->getFluent()->where('kod NOT IN %l', $list2)->fetchAll();
    }

    public function actionDeleteOldProducts()
    {
        $db   = $this->productModel->getConnection();
        $list = $this->importModel->getFluent()->fetchPairs('Kod', 'Kod');

        $query          = $this->productModel->productParamModel->getFluent('code,id_product')->where('code NOT IN %l', $list);
        $query2         = clone $query;
        $deleteCodes    = $query2->fetchPairs('code', 'code');
        $deleteProducts = $query->fetchAll();
        $db->delete('product_param')->where('code IN %l', $deleteCodes)->execute();

        foreach ($deleteProducts as $p) {
            $hasMoreVariant = $db->fetchSingle("SELECT COUNT(id_product_param) FROM product_param WHERE id_product = %i", $p['id_product']);
            //ak ma iba jednu alebo 0 variant, zmazeme cely product, ale ak ma viac, nechavame
            if ($hasMoreVariant < 2) {
                $this->productModel->delete($p['id_product']);
            }
        }

        $this->flashMessage('Podukty boli vymazene');
        $this->redirect('default');
    }

    public function actionImportProducts()
    {
        $id_lang             = 1;
        $id_product_supplier = 1;

        //vyber iba tie produkty, ktore su nove, cize nie su uz v productParam tabulke

        $allProductCodes = $this->productModel->productParamModel->getFluent('code')->fetchPairs('code', 'code');

        $list = $this->importModel->getFluent()->where('[Kod] NOT IN %l', $allProductCodes)->limit('8000')->fetchAll();

        foreach ($list as $k => $l) {

            $id_product_param = $this->productModel->productParamModel->getIdByCode($l['Kod']);
//            dump($l['Kod']);
//            exit;
            // ak neexistuje product
            if (!$id_product_param) {
                $group_code = $this->getGroupCode($l);
                $id         = $this->productModel->getIdByGroupCode($group_code);
                //ak neexituje groupCode
                if (!$id) {

                    $id_product_mark = $this->productModel->productMarkModel->getIdByName($l['Vyrobce']);
                    // ak neni znacka
                    if (!$id_product_mark) {
                        $id_product_mark = $this->productModel->productMarkModel->insertAndReturnLastId(['name' => $l['Vyrobce']]);
                    }

                    $arr = [
                        'id_lang'             => $id_lang,
                        'id_product_supplier' => $id_product_supplier,
                        'id_product_mark'     => $id_product_mark,
                        'group_code'          => $group_code,
                        'news'                => ($l['Novinka'] == 'Ano') ? 1 : 0,
                        'id_vat'              => '1',
                        'name'                => $l['Nazev'],
                        'description'         => $l['Popis'],
                        'long_description'    => $l['Podrobnosti'],
                        'meta_title'          => $l['Nazev'],
                        'meta_description'    => Strings::truncate($l['Popis'], 50),
                        'link_rewrite'        => Strings::webalize($l['Nazev']),
                        'status'              => 'live'
                    ];

                    $id = $this->productModel->add($arr);
                }

                $arr = [
                    'id_product'      => $id,
                    'code'            => $l['Kod'],
                    'is_on_stock'     => ($l['Sklad'] == 'Ano') ? 1 : 0,
                    'price'           => $l['Cena'] * $l['Nedelitelne_mnozstvi'],
                    'unit_of_measure' => $l['Bal2'],
                    'image'           => $l['Img'],
                    'EAN'             => $l['EAN'],
                    'packing'         => $l['Mj']
                ];

                $this->productModel->productParamModel->insert($arr);

                // priradenie do kategorii

                //prva uroven
                $l['Tree1'] = trim($l['Tree1']);
                $idCat1     = $this->categoryModel->getIdByName($l['Tree1']);
                if ($idCat1) {
                    $l['Tree2'] = trim($l['Tree2']);
                    $this->categoryProductModel->insertIfNotExist(['id_category' => $idCat1, 'id_product' => $id]);
                    $idCat2 = $this->categoryModel->getIdByName($l['Tree2'], $idCat1);
                    if ($idCat2) {
                        $l['Tree3'] = trim($l['Tree3']);
                        $this->categoryProductModel->insertIfNotExist(['id_category' => $idCat2, 'id_product' => $id]);
                        $idCat3 = $this->categoryModel->getIdByName($l['Tree3'], $idCat2);
                        if ($idCat3) {
                            $this->categoryProductModel->insertIfNotExist(['id_category' => $idCat3, 'id_product' => $id]);
//                            $idCat4 = $this->categoryModel->getIdByName($l['Tree4'], $idCat3);
//                            if($idCat4){
//                                $this->categoryProductModel->insertIfNotExist(['id_category'=>$idCat4,'id_product'=>$id]);
//                            }
                        }
                    }
                }

            }
        }

        $this->flashMessage('Import prebehol úspešne. Počet produktov:' . count($list));
        $this->redirect('default');
    }

    private function getGroupCode($v)
    {
        return Strings::webalize($v['Tree1'] . '-' . $v['Tree2'] . '-' . $v['Tree3'] . '-' . $v['Nazev']);
    }

    public function actionSavePlacekXml()
    {
        $url = 'http://data.placek.eu/Client/Catalog.aspx?source=customer&jmeno=AQUACITYSK&heslo=LS7DG3&typ=xml';

        $filename = $this->context->parameters['webimages']['uploadDir'] . '/../xml/' . self::PLACEK_FILENAME;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $content = curl_exec($ch);

        file_put_contents($filename, $content);
        curl_close($ch);
        $this->flashMessage('Import je v poriadku');
        $this->redirect('default');
    }

    public function actionFixIsMainInProductParam()
    {
        $list              = $this->productModel->getFluent('id_product')
//            ->join('product_param')->using('id_product')
            ->where('(SELECT 1 FROM product_param WHERE product.id_product = product_param.id_product AND is_main = 1) IS NULL')
//        ->limit(100)
            ->fetchAll();
        $productParamModel = $this->productModel->productParamModel;
        foreach ($list as $l) {
//            $connection->test("UPDATE product_param SET id_main = 1 WHERE id_product_param = (SELECT id_product_param FROM product_param WHERE id_product = %i",$l['id_product']," ORDER BY price))");
            $id_product_param = $productParamModel->getFluent()->where('id_product = %i', $l['id_product'])->orderBy('price')->fetchSingle();
            $productParamModel->update(['is_main' => 1], $id_product_param);
        }

//        dump($list);

        $this->flashMessage('Hlavne podprodukt bol spravne urceny');
        $this->redirect('default');

    }

    public function actionUpdatePlacekPriceStock()
    {

        $list              = $this->importModel->getFluent('Cena,Nedelitelne_mnozstvi,Kod,Sklad')->fetchAll();
        $productParamModel = $this->productModel->productParamModel;
        foreach ($list as $l) {
            $id_product_param = $productParamModel->getIdByCode($l['Kod']);
            $productParamModel->update([
                'is_on_stock' => ($l['Sklad'] == 'Ano') ? 1 : 0,
                'price'       => $l['Cena'] * $l['Nedelitelne_mnozstvi'],
            ], $id_product_param);
        }

        $this->flashMessage('Ceny boli aktualizovane');
        $this->redirect('default');
    }

    public function actionCreateCategoryTree()
    {
        $id_lang = 1;
        $list    = $this->importModel->getFluent()->limit('0,30000')->fetchAll();
        foreach ($list as $l) {
            $id1 = $this->insertCategoryData($l['Tree1'], $id_lang, null);
            $id2 = $this->insertCategoryData($l['Tree2'], $id_lang, $id1);
            $id3 = $this->insertCategoryData($l['Tree3'], $id_lang, $id2);
//            $id4 = $this->insertCategoryData($l['Tree4'], $id_lang, $id3);
        }
    }

    private function insertCategoryData($name, $id_lang, $id_parent = null)
    {
        $name = trim($name);
        $id   = $this->categoryModel->getIdByName($name, $id_parent);

        if (!$id AND $name AND $id_parent !== false) {

            $arr = [
                'id_lang'      => $id_lang,
                'name'         => $name,
                'meta_title'   => $name,
                'link_rewrite' => Strings::webalize($name),
                'id_parent'    => $id_parent,
                'status'       => 'live'
            ];

            $this->categoryModel->add($arr);
        }

        return $id;
    }

    public function actionParse()
    {
        $filename = $this->context->getParameters()['wwwDir'] . '/uploaded/system_files/xml/' . self::PLACEK_FILENAME;

        $xml = simplexml_load_file($filename) or die("Error: Cannot create object");

        $this->importModel->truncate();

        $arr     = [];
        $counter = 0;

        foreach ($xml as $xmlRow) {

            $row = ImportTools::arrayMapObjectToArray($xmlRow);
//            dump($row);
//            exit;
            $row['EAN']  = (isset($row['EANs']) AND isset($row['EANs']['EAN'])) ? $row['EANs']['EAN'] : '';
            $row['EAN2'] = '';
            if (is_array($row['EAN'])) {
                $row['EAN2'] = $row['EAN'][1];
                $row['EAN']  = $row['EAN'][0];
            }
            unset($row['EANs']);

            $row['Nedelitelne_mnozstvi'] = (int)$row['Nedelitelne_mnozstvi'];

//            exit;
            $arr[] = $row;
            if ($counter < 2) {
                $counter++;
            } else {
                $counter = 0;
                $this->importModel->connection->query("INSERT INTO [" . $this->importModel->getTableName() . "] %ex", $arr);
                $arr = [];
            }
        }

        if (!empty($arr)) {
            $this->importModel->connection->query("INSERT INTO [" . $this->importModel->getTableName() . "] %ex", $arr);
        }

        echo 'Parsed sucessfuly';
        exit;
    }

    public function actionFixCategoryUrl()
    {
        $all = $this->categoryModel->getFluent()->join('category_lang')->using('(id_category)')->where('id_lang = 1')->fetchAssoc('id_category');

        $depths = $this->categoryModel->getFluent('DISTINCT(depth)')->fetchAll();
        foreach ($depths as $depth) {
            $list1 = $this->categoryModel->getFluent()->where('depth = %i', $depth['depth'])->fetchAll();
            foreach ($list1 as $l) {
                $parentSlug = '';
                if (isset($all[$l['id_parent']])) {
                    $parentSlug = $all[$l['id_parent']]['slug'];

                    if ($parentSlug == '') {
                        $parentSlug = $all[$l['id_parent']]['link_rewrite'];
                    }
                }

                if ($parentSlug != '') {
                    $newSlug = $parentSlug . '/' . $all[$l['id_category']]['link_rewrite'];
                } else {
                    $newSlug = $all[$l['id_category']]['link_rewrite'];
                }

                $all[$l['id_category']]['slug'] = $newSlug;
                $this->categoryModel->getCategoryLangModel()->update(['slug' => $newSlug], $id_lang = 1, $l['id_category']);
            }
        }
    }

    public function actionRepairProductRewriteFromTitle()
    {
        $db   = $this->productModel->getConnection();
        $list = $db->select('link_rewrite, name, id_product')->from('product_lang')->fetchAll();
        foreach ($list as $l) {
            $db->query("UPDATE product_lang SET", ['link_rewrite' => Strings::webalize($l['name'])], "WHERE id_product = %i", $l['id_product']);
        }
        exit;
    }

    public function actionRepairCategoryRewriteFromTitle()
    {
        $db   = $this->productModel->getConnection();
        $list = $db->select('link_rewrite, name, id_category')->from('category_lang')->fetchAll();
        foreach ($list as $l) {
            $db->query("UPDATE category_lang SET", ['link_rewrite' => Strings::webalize($l['name'])], "WHERE id_category = %i", $l['id_category']);
        }
        exit;
    }

    public function actionRefreshProductRewriteLinks()
    {
        $connection = $this->productModel->getConnection();
        $connection->query("TRUNCATE product_param_rewrite");
        $connection->query("INSERT INTO product_param_rewrite (slug, id_product_param) 
SELECT CONCAT(link_rewrite,'-',EAN) AS slug, id_product_param FROM product_param JOIN product_lang USING(id_product)
WHERE id_lang = 1
");
    }

    public function injectImportModel(ImportPlacekModel $model)
    {
        $this->importModel = $model;
    }

    public function injectCategoryModel(CategoryModel $model)
    {
        $this->categoryModel = $model;
    }

    public function injectProductModel(ProductModel $model)
    {
        $this->productModel = $model;
    }

    public function injectCategoryProductModel(CategoryProductModel $categoryProductModel)
    {
        $this->categoryProductModel = $categoryProductModel;
    }

    protected function createComponentFormImportFromText($name)
    {
        $f = new Form();
        $f->addTextArea('text', 'Text na import');
        $f->addSubmit('btn', 'Odoslat');
        $f->onSuccess[] = array($this, 'handleFormImport');

        return $f;
    }

}
