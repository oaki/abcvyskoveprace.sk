<?php

namespace App\AdminModule\EshopModule\Forms\ProductForm;

use App\Model\Entity\Eshop\CategoryModel;
use App\Model\Entity\Eshop\ProductModel;
use App\Model\Entity\LangModel;
use Nette\Application\UI\Form;

class ProductForm extends Form
{

    private $model, $id_product, $categoryModel, $langModel;

    public $langs;

    function __construct($parent = null, $name = null, ProductModel $model, CategoryModel $categoryModel, LangModel $langModel)
    {
        parent::__construct($parent, $name);

        $this->model         = $model;
        $this->categoryModel = $categoryModel;
        $this->langModel     = $langModel;

        $this->langs = $langModel->getFluent()->fetchAll();

        $this->addFields();
//		$this->getElementPrototype()->class = 'ajax';
    }

//	public function addTree($name, $label = NULL, $tree, $size = NULL)
//	{
//		$this[$name] = new \EshopModule\Form\CategorySelectBox($label, $tree, $size, $this->getPresenter()->template);
//
//	}

    function setModel($model)
    {
        $this->model = $model;
    }

    function setCategoryModel($categoryModel)
    {
        $this->categoryModel = $categoryModel;
    }

    function setIdProduct($id_product)
    {
        $this->id_product = $id_product;
//		dde($this->model->get($id_product));
        $this->setDefaults($this->model->getFormValues($id_product));
    }

    function addFields()
    {

        $this->addSelect('id_product_mark', 'Značka', $this->model->productMarkModel->getFluent('id_product_mark,name')->fetchPairs('id_product_mark', 'name'));

//		$this->addText('packing', 'Obchodné balenie');
//		$this->addText('in_layer', 'Vo vrstve');
//		$this->addText('on_pallet', 'Na palete');
//		$this->addText('ean', 'EAN');
//		$this->addText('our_id', 'ID');
//		$this->addText('expired_date', 'Expiračná doba ');
        $this->addCheckbox('sale', 'Akcia');
        $this->addText('sale_percent', 'Zľava %');
        $this->addCheckbox('news', 'Novinka');
        $this->addCheckbox('our_tip', 'TOP');
        $this->addCheckbox('home', 'Zobraziť na úvode');

//		$items = $this->categoryModel->fetchPairsForSelect($this->langModel->getDefaultLang());
//		$tree = $this->categoryModel->getTree($this->langModel->getDefaultLang());
//		$this->addTree('categories', 'Kategórie', $items, $tree);
//		dde($items);
//		$this->addMultiSelect('categories','Kategórie',$items,300);

        //pridanie poli ktore sa budu prekladat
        $lang_container = $this->addContainer('product_lang');

        foreach ($this->langs as $lang) {

            $c = $lang_container->addContainer($lang->id_lang);
//			$c->currentGroup = $this->addGroup($lang->name, FALSE);
//			$c->currentGroup = $group;

            $c->addHidden('id_lang', $lang->id_lang);
            $c->addText('name', 'Názov (' . $lang->iso . ')')->setAttribute('class', 'span9');

            $c->addTextarea('description', 'Popis (' . $lang->iso . ')')
                ->setAttribute('class', 'span12');
//					->setAttribute('class', 'mceEditor');

            $c->addTextarea('long_description', 'Dlhý popis (' . $lang->iso . ')')
                ->setAttribute('class', 'mceEditor');

            $c->addText('link_rewrite', 'Url (' . $lang->iso . ')')
                ->setAttribute('class', '');

            $c->addText('meta_title', 'Meta title (' . $lang->iso . ')');

            $c->addText('meta_keywords', 'Meta keywords (' . $lang->iso . ')')
                ->setAttribute('class', '');

            $c->addText('meta_description', 'Meta description (' . $lang->iso . ')')
                ->setAttribute('class', '');
        }

        $this->addHidden('id_product');
        $this->addCheckboxList('categories', 'Kategorie', $this->categoryModel->fetchPairsForSelect($this->langModel->getDefaultLang()));

//		$this->addGroup('Btn');
        $this->addSubmit('btn', 'Uložiť')
            ->setAttribute('class', 'btn btn-success');

        $this->onSuccess[] = array($this, 'handleSave');
    }

    function handleSave(self $form)
    {
        $values = $form->getValues();

        $this->model->update($values, $this->id_product);

        $this->getPresenter()->flashMessage('Product bol upravený.');

        if ($this->getPresenter()->isAjax()) {
            $this->getPresenter()->redrawControl('form');

            $this->getPresenter()->redrawControl('flashmessage');
        } else {
            $this->getPresenter()->redirect('this');
        }
    }

}