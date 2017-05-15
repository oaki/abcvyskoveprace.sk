<?php

namespace App\AdminModule\EshopModule\Forms\CategoryForm;

use App\Model\Entity\Eshop\CategoryModel;
use App\Model\Entity\LangModel;
use Nette\Application\UI\Form;

class CategoryForm extends Form
{

    private $model, $id_category, $langModel;

    function __construct($parent = null, $name = null, CategoryModel $model, LangModel $langModel)
    {
        parent::__construct($parent, $name);

//		$this->setRenderer(new BootstrapRenderer);

        $this->model     = $model;
        $this->langModel = $langModel;

        $this->addFields();

        $this->getElementPrototype()->class = 'ajax';
    }

    function setIdCategory($id_category)
    {
        $this->id_category = $id_category;
        $this->setDefaults($this->model->fetch($id_category));
    }

    function addFields()
    {

        $langs = $this->langModel->getFluent()->fetchAll();

        $lang_container = $this->addContainer('category_lang');

        foreach ($langs as $lang) {

            $c               = $lang_container->addContainer($lang->id_lang);
            $c->currentGroup = $this->addGroup($lang->name, false);

//			$c->currentGroup = $group;

            $c->addHidden('id_lang', $lang->id_lang);
            $c->addText('name', 'Názov')
                ->addRule(Form::FILLED, 'Názov nesmie byť prázdny')
                ->setAttribute('class', 'span9');

            $c->addTextarea('description', 'Popis')
                ->setAttribute('class', 'span9');

            $c->addText('link_rewrite', 'Url')
                ->setAttribute('class', '');

            $c->addText('meta_title', 'Meta title')
                ->setAttribute('class', 'span9');

            $c->addText('meta_keywords', 'Meta keywords')
                ->setAttribute('class', 'span9');

            $c->addText('meta_description', 'Meta description')
                ->setAttribute('class', 'span9');

            $c->addSelect('show_in_menu', 'Zobraziť v menu', ['0' => 'nie', '1' => 'ano']);

        }

        $this->addHidden('id_category');

        $this->addGroup('Btn');
        $this->addSubmit('btn', 'Uložiť')
            ->setAttribute('class', 'btn btn-success');

        $this->onSuccess[] = array($this, 'handleSave');
    }

    function handleSave(Form $form)
    {
        $values = $form->getValues();

        $this->model->update($values, $this->id_category);

        $this->getPresenter()->flashMessage('Kategória bol upravená.');

        if ($this->getPresenter()->isAjax()) {
            $this->getPresenter()->redrawControl('form');
            $this->getPresenter()['sortableEshopCategory']->redrawControl();
            $this->getPresenter()->redrawControl('flashmessage');
        } else {
            $this->getPresenter()->redirect('this');
        }

    }

}