<?php

/**
 * Description of ProductPresenter
 *
 * @author oaki
 */
namespace App\AdminModule\EshopModule\Presenters;

use App\Components\Backend\DataGrid\DataGrid;
use App\Model\Entity\Eshop\CategoryModel;
use App\Model\Entity\Eshop\ProductModel;
use App\Model\Entity\Eshop\ProductParamModel;

class ProductPresenter extends BasePresenter
{

    /**
     * @var ProductModel $productModel
     */
    protected $productModel;

    protected $categoryModel;

    protected $productParamModel;

    function actionAdd()
    {
        $new_id = $this->productModel->add([
            'id_lang' => 1,
            'id_vat'  => 1
        ]);

        $this->redirect('default', $new_id);
    }

    public function renderDefault($id)
    {
//        $this->template->product = $this->productModel->fetch($id);
//
//        if (!$this->template->product) {
//            $this->flashMessage('Product neexistuje');
//            $this->redirect('Eshop:Homepage:default');
//        }

        $this->template->langs = $this->langModel->getAll();

//        $items = $this->categoryModel->fetchPairsForSelect($this->langModel->getDefaultLang());
        $this->template->categories        = $this->categoryModel->getTree($this->langModel->getDefaultLang());
        $this->template->productCategories = $this->productModel->getProductCategories($id);

    }

    function createComponent($name)
    {
        switch ($name) {
            case 'productForm':

                $f = new \App\AdminModule\EshopModule\Forms\ProductForm\ProductForm($this, $name, $this->productModel, $this->categoryModel, $this->langModel);

                $f->setIdProduct($this->id);

                return $f;
                break;

            case 'productParamGrid':
                $grid = new DataGrid($this, $name);
                $grid->setPrimaryKey('id_product_param');

                $source = $this->productModel->productParamModel
                    ->getFluent()
                    ->where('id_product = %i', $this->id);

                $fields = [
                    'code' => 'Kód',

                    'price'   => 'Cena',
                    'image'   => 'Obrázok',
                    'stock'   => 'Sklad',
                    'packing' => 'Obsah',
                    'EAN'     => 'EAN',
                ];

                $controlFields = function ($container) use ($fields) {
                    foreach ($fields as $name => $value) {
                        $container->addText($name, $value);
                    }
                };

                $grid->addInlineAdd()->onControlAdd[] = $controlFields;

                $presenter = $this;

                $grid->getInlineAdd()->onSubmit[] = function ($values) use ($presenter, $grid) {
                    $presenter->flashMessage("Parameter bol pridaný", 'success');
                    $values['id_product'] = $presenter->id;
                    $presenter->productParamModel->insert($values);
                    $presenter->redrawControl('flashmessage');
                    $grid->redrawControl();
                };

                $grid->addInlineEdit()->onControlAdd[] = $controlFields;

                $grid->getInlineEdit()->onSetDefaults[] = function ($container, $item) {
                    $container->setDefaults($item);
                };

                $grid->getInlineEdit()->onSubmit[] = function ($id, $values) use ($presenter) {
                    $presenter->flashMessage("Parameter bol pridaný", 'success');
                    $presenter->productParamModel->update($values, $id);
                    $presenter->redrawControl('flashmessage');
                };

                $grid->setDataSource($source);

                foreach ($fields as $name => $value) {
                    $grid->addColumnText($name, $value);
                }

                return $grid;
                break;

            case 'file':
                $f = new \App\Components\Backend\File\FileControl($this, $name);

                $f->setIdFileNode($this->getService('FileNode')->getIdFileNode($this->id, 'Product'));

                $f->addInput(array('type' => 'text', 'name' => 'name', 'css_class' => 'input-medium', 'placeholder' => 'Sem umiestnite popis'));

                $f->saveDefaultInputTemplate();

                return $f;
                break;

            default :
                return parent::createComponent($name);
                break;
        }

    }

    public function injectProductModel(ProductModel $productModel)
    {
        $this->productModel = $productModel;
    }

    public function injectCategoryModel(CategoryModel $categoryModel)
    {
        $this->categoryModel = $categoryModel;
    }

    public function injectProductParamModel(ProductParamModel $productParamModel)
    {
        $this->productParamModel = $productParamModel;
    }

}