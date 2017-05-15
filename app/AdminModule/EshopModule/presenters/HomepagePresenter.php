<?php

namespace App\AdminModule\EshopModule\Presenters;

//use App\AdminModule\EshopModule\Components\ProductGrid\ProductGrid;
use App\AdminModule\EshopModule\Components\SortableEshopCategory\SortableEshopCategoryControl;
use App\Model\Entity\Eshop\CategoryModel;
use App\Model\Entity\Eshop\ProductModel;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\DataModel;

class HomepagePresenter extends BasePresenter
{

    public $id_category;

    protected $categoryModel;

    protected $productModel;

    static function getPersistentParams()
    {
        return array('id_category');
    }

    function renderDefault()
    {

    }

    protected function createComponentProductGrid($name)
    {
        DataGrid::$icon_prefix = 'icon icon-';
        $grid                  = new DataGrid($this, $name);
        $grid->setDefaultPerPage(30);

        $id_lang = $this->langModel->convertIsoToId($this->lang);
        $source  = $this->productModel
            ->getFluent('
            product.id_product AS id,
            product.status,
            product_lang.name,
            product.group_code,
            product_mark.name AS product_mark_name,
            product_param.EAN
            ')
            ->join('product_lang')->using('(id_product)')
            ->leftJoin('category_product')->using('(id_product)')
            ->leftJoin('product_mark')->using('(id_product_mark)')
            ->join('product_param')->using('(id_product)')
            ->where('product_lang.id_lang = %i', $id_lang, '%if', $this->id_category != '', '
					AND category_product.id_category = %i', $this->id_category, '%end
					AND product.status != "not_in_system"')
            ->groupBy('product.id_product');

//        dump($source->test());
//        exit;
//        $source = new DibiFluentDataSourc($q, $primary_key_name = 'id_product');
        //Předáme zdroj
//        $dataModel = new DataModel($source, 'id_product');
        $grid->setPrimaryKey('id');
        $grid->setDataSource($source);

        $grid->setRememberState(false);

        $grid->addColumnText('name', 'Name');

        $grid->addFilterText('name', 'Name:', 'name')
            ->setCondition(function ($fluent, $value) {
                /**
                 * The data source is here DibiFluent
                 * No matter what data source you are using,
                 * prepared data source will be passed as the first parameter of your callback function
                 */
                $fluent->where('product_lang.name LIKE %~like~', $value);
            });

        $grid->addColumnText('EAN', 'EAN');
        $grid->addFilterText('EAN', 'EAN:', 'EAN');

        $grid->addColumnText('product_mark_name', 'Značka');
//        $grid->addColumnText('product.group_code', 'Kód');
        $grid->addColumnText('status', 'Status');
        $grid->addFilterSelect('status', 'Status:', ['live' => 'Live', 'invisible' => 'Neaktívne', 'deleted' => 'Vymazané']);

//        $grid->addActionCallback('Product:default', 'Edit', function(){
//            var_dump(func_get_args());
//            exit;
//        })->setIcon('pencil')
//            ->setTitle('Edit row');

        $grid->addAction('Product:default', 'Edit')->setIcon('pencil')
            ->setTitle('Edit row');

        $grid->addAction('delete', '', 'deleteProduct!')
            ->setIcon('trash')
            ->setTitle('Smazat')
            ->setClass('btn  btn-xs btn-danger ajax')
            ->setConfirm('Naozaj chcete vymazať produkt %s?', 'name');

        return $grid;
//		return new ProductGrid( $this->getService('ProductModel'), $this->id_category, $this->getService('LangModel'));
    }

    public function handleDeleteProduct($id)
    {
        $this->productModel->delete($id);
        $this->flashMessage('Produkt bol vymazaný.');
        $this['productGrid']->redrawControl();
        $this->redrawControl('flashmessage');
    }

    function createComponent($name)
    {
        switch ($name) {
            case 'sortableEshopCategory':
                return new SortableEshopCategoryControl($this, $name, $this->categoryModel, $this->langModel);
                break;

            default :
                return parent::createComponent($name);
                break;
        }
    }

    public function injectCategoryModel(CategoryModel $categoryModel)
    {
        $this->categoryModel = $categoryModel;
    }

    public function injectProductModel(ProductModel $productModel)
    {
        $this->productModel = $productModel;
    }
}
