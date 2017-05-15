<?php

namespace App\AdminModule\EshopModule\Presenters;

use App\AdminModule\EshopModule\Components\SortableEshopCategory\SortableEshopCategoryControl;
use App\AdminModule\EshopModule\Forms\CategoryForm\CategoryForm;
use App\Components\Backend\File\FileControl;
use App\Model\Entity\Eshop\CategoryModel;

class CategoryPresenter extends BasePresenter
{

    public $id;

    protected $categoryModel;

    static function getPersistentParams()
    {
        return array('id');
    }

    function actionAdd()
    {
        $new_id = $this->categoryModel->insertAndReturnLastId(array());
        $this->redirect('edit', array('id' => $new_id));
    }

    function renderEdit($id)
    {
        /*
         * pridanie CSS a JS pre sortableMenu
         */
//		$this['header']['js']->addFile('/jquery/jquery.mjs.nestedSortable.js');
//		$this['header']['css']->addFile('/admin/sortablemenu/sortablemenu.css');
    }

    function createComponent($name)
    {

        switch ($name) {
            case 'sortableEshopCategory':
                return new SortableEshopCategoryControl($this, $name, $this->categoryModel, $this->langModel);
                break;

            case 'file':
                $f = new FileControl($this, $name);

                $f->setIdFileNode($this->getService('FileNode')->getIdFileNode($this->id, 'Category'));

                $f->addInput(array('type' => 'text', 'name' => 'name', 'css_class' => 'input-medium', 'placeholder' => 'Sem umiestnite popis'));

                $f->saveDefaultInputTemplate();

                return $f;
                break;

            case 'categoryForm':
                $f = new CategoryForm($this, $name, $this->categoryModel, $this->langModel);
                $f->setIdCategory($this->id);

                return $f;
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

}
