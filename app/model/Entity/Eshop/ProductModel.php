<?php
namespace App\Model\Entity\Eshop;

use App\Model\BaseDbModel;
use App\Model\Entity\File\FileNodeModel;
use App\Model\Entity\IJoinFileNodeModel;
use DibiConnection;
use Nette\Caching\Storages\FileStorage;

class ProductModel extends BaseDbModel implements IJoinFileNodeModel
{

    public $productLangModel, $productParamModel, $productMarkModel, $fileNodeService;

    protected $table = 'product';

    function __construct(DibiConnection $connection, FileStorage $cache, ProductLangModel $productLangModel,
                         ProductParamModel $productParamModel,
                         ProductMarkModel $productMarkModel, FileNodeModel $fileNodeService)
    {
        parent::__construct($connection, $cache);

        $this->productLangModel  = $productLangModel;
        $this->productMarkModel  = $productMarkModel;
        $this->productParamModel = $productParamModel;
        $this->fileNodeService   = $fileNodeService;
    }

//    function getMarks()
//    {
//        return $this->productMarkModel->getFluent('id_product_mark,name')->fetchPairs('id_product_mark', 'name');
//    }

    public function getAllInfo($id_product_param, $id_lang)
    {
        $list = $this->loadWithSave(
            'getAllInfo(' . $id_product_param . ', ' . $id_lang . ')',
            function () use ($id_product_param, $id_lang) {
                $l = $this->productParamModel->fetch($id_product_param);
                if (!$l) {
                    return false;
                }
                $l['info'] = $this->fetch($l['id_product'], $id_lang);

                return $l;
            });

        $list['price'] = $this->productParamModel->getPrice($id_product_param);

        return $list;
    }

    public function fetch($id_product, $id_lang = null)
    {
        if ($id_lang === null) {
            throw new \Exception('id lang is not defined.');
        }

        $l = $this->getFluent('*')
            ->join('product_lang')
            ->on('product.id_product = product_lang.id_product AND product_lang.id_lang = %i', $id_lang)
//            ->leftJoin('product_mark')->using('(id_product_mark)')
            ->where('product.id_product = %i', $id_product)
            ->fetch();

        if (!$l) {
            return false;
        }

        $l['files'] = $this->getImages($id_product);

        $l['mark'] = $this->productMarkModel->get($l['id_product_mark']);

        //$l['price'] = $this->productParamModel->getFluent('price')->where('id_product = %i', $id_product)->orderBy('price')->fetchSingle();

        $l['variants'] = $this->getVariants($id_product);

        return $l;

    }

    private function getImages($id)
    {
        $l = $this->fileNodeService->getFiles($this->getIdFileNode($id));

        return $l;
    }

    function getIdFileNode($id)
    {
        return $this->fileNodeService->getIdFileNode($id, 'Product');
    }

    public function getVariants($id_product)
    {
        return $this->productParamModel->getFluent()
            ->where('id_product = %i', $id_product)
            ->fetchAll();
    }

    public function getTopProductsRand($id_lang)
    {
        $list = $this->getFluent('id_product')->where('our_tip = 1')->fetchAll();
        foreach ($list as $k => $l) {
            $list[$k] = $this->productParamModel->fetchByProductId($l['id_product'], $id_lang);
        }

        return $list;
    }

    /**
     * Pouzite pre MENU
     *
     * @param $id_lang
     *
     * @return array
     */
    public function getTopProductsToMenu($id_lang)
    {
        $list = $this->getFluent('id_product')->where('our_tip = 1')->limit(2)->fetchAll();
        foreach ($list as $k => $l) {
            $list[$k] = $this->productParamModel->fetchByProductId($l['id_product'], $id_lang);
        }

        return $list;
    }

    public function getIdByGroupCode($groupCode)
    {
        return $this->getFluent('id_product')->where('group_code = %s', $groupCode)->fetchSingle();
    }

    public function add($values)
    {
        $productRows       = $this->getTableRows();
        $productLangRows   = $this->productLangModel->getTableRows();
        $productValues     = [];
        $productLangValues = [];

        foreach ($values as $k => $v) {
            if (isset($productRows[$k])) {
                $productValues[$k] = $v;
            }

            if (isset($productLangRows[$k])) {
                $productLangValues[$k] = $v;
            }
        }

        $id_product                      = $this->insertAndReturnLastId($productValues);
        $productLangValues['id_product'] = $id_product;
        $this->productLangModel->insert($productLangValues);

        return $id_product;
    }

    function getByLang($id, $id_lang)
    {
        $key = 'getByLang(' . $id . ', ' . $id_lang . ')';

        $r = $this->loadCache($key);
        if ($r) {
            return $r;
        }

        $c    = $this->get($id);
        $lang = $c['product_lang'];
        unset($c['product_lang']);

        foreach ($lang[$id_lang] as $k => $i) {
            $c[$k] = $i;
        }

        return $this->saveCache($key, $c);
    }

    function getFormValues($id)
    {

        $l = $this->getFluent()->where('id_product = %i', $id)->fetch();

        if (!$l) {
            return false;
        }

        $l['categories']   = $this->getProductCategories($id);
        $l['product_lang'] = $this->productLangModel->getFluent()->where("id_product = %i", $id)->fetchAssoc('id_lang');

//        $l['files'] = $this->getImages($id);

        return $l;
    }

    public function getProductCategories($id_product)
    {
        return $this->connection->select('id_category')->from('category_product')->where('id_product = %i', $id_product)->fetchPairs('id_category', 'id_category');
    }

    public function getAlternativeProduct($id_product)
    {
        $categories = $this->connection->select('id_category')->from('category_product')->where('id_product = %i', $id_product)->fetchAssoc('id_category');

        return $this->getFluent('id_product_param')
            ->join('product_param')->using('(id_product)')
            ->join('category_product')->using('(id_product)')
            ->where('id_product != %i', $id_product, 'AND is_main = 1', 'AND id_category IN %l', $categories)
            ->limit(10)
            ->orderBy('RAND()')
            ->fetchAll();
    }

    function update($values, $id)
    {

        if (isset($values['product_lang'])) {

            foreach ($values['product_lang'] as $product_lang) {

                $this->productLangModel->update($product_lang, ['id_lang' => $product_lang['id_lang'], 'id_product' => $id]);
            }
            unset($values['product_lang']);
        }

        if (isset($values['categories']) AND is_array($values['categories'])) {
            $this->connection->begin();
            $this->connection->delete('category_product')->where('id_product = %i', $id)->execute();
            foreach ($values['categories'] as $id_category) {
                $this->connection->insert('category_product', array('id_product' => $id, 'id_category' => $id_category))->execute();
            }
            $this->connection->commit();
            unset($values['categories']);
        }

        parent::update($values, $id);

        $this->putInToTheSystem($id);
    }

    /**
     * Vymazanie zo systemu moduly ktore neboli ulozene
     */

    function putInToTheSystem($id)
    {
        //over ci je status not_in_system, ak je prepis na live
        $l = $this->getFluent('1')->where("id_product = %i", $id, "AND status = 'not_in_system'")->fetchSingle();
        if ($l) {
            $this->update(array('status' => 'live'), $id);
        }
    }

    function deleteUnsaveProduct()
    {
        //vymaze az po 12 hodinach ak sa nezmeni stav
        $list = $this->getFluent()->where("status = 'not_in_system' AND add_date < ( NOW() - 60*12 )")->fetchAll();

        if (!empty($list)) {
            foreach ($list as $l) {
                $this->delete($l['id_product']);
            }
        }
    }
}