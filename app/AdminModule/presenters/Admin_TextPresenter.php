<?php
namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;

class Admin_TextPresenter extends AdminPresenter
{

    private $textModel;

    function startup()
    {
        parent::startup();
        $this->textModel = $this->context->createInstance('TextModel');

    }

    function renderEdit($id)
    {
        $this['textForm']->setDefaults($this->textModel->fetch($id));
    }

    function handleSaveText(Form $form)
    {
        $values = $form->values;
        $this->textModel->update($values, $values['id_text']);
        $this->flashMessage('Text bol upravený');
        $this->redirect('this');
    }

    function createComponent($name)
    {
        switch ($name) {

            case 'textForm':
                $f = new TwitterBootstrapForm;
                $f->addText('name', 'Názov')
                    ->setAttribute('class', 'span9');

                $f->addTextArea('text', 'Text')
                    ->setAttribute('class', 'span9');
                $f->addSubmit('btn', 'Uložiť');
                $f->addHidden('id_text');
                $f->onSuccess[] = array($this, 'handleSaveText');

                return $f;
                break;
            case 'simpleGrid':
                $presenter = $this;

                $grid = new SimpleGrid($this->textModel->getFluent());

                $grid->addColumn('id_text', 'ID');
                $grid->addColumn('name', 'Názov');
                $grid->addColumn('text', 'Text');

                $grid->addAction('edit', array(
                        'class'        => 'btn',
                        'i_class'      => 'icon-pencil',
                        'link_builder' => function ($row) use ($presenter) {
                            return $presenter->link('edit', array('id' => $row->id_text));
                        })
                );

                return $grid;
                break;
            default:
                return parent::createComponent($name);
                break;
        }
    }

}