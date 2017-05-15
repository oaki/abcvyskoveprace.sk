<?php
namespace App\AdminModule\DashboardModule\Presenters;

use App\Components\Backend\File\FileControl;
use App\Components\SimpleGrid;
use Nette\Application\UI\Form;

/**
 * Description of DashboardPresenter
 *
 * @author oaki
 */
class HomepagePresenter extends Admin_Dashboard_BasePresenter
{

    private $fileNode;

    function startup()
    {
        parent::startup();
        $this->fileNode = $this->getService('FileNode');
    }

    function renderDefault()
    {

    }

    function handleDelete($id)
    {

        $this->dashboard->delete($id);

        $this->flashMessage('Správa bol úspešne zmazaná.');

        if ($this->isAjax()) {
            $this->redrawControl('flashmessage');
            $this['simpleGrid']->redrawControl();
        } else {
            $this->redirect('this');
        }
    }

    function actionAdd(Form $form)
    {
        $values = $form->getValues();

        $autor = $this->user->getIdentity()->name . ' ' . $this->user->getIdentity()->surname;

        $arr = array(
            'add_date' => new \DibiDateTime(),
            'autor'    => $autor,
            'text'     => $values->text
        );

        $this->dashboard->insert($arr);

        $this->flashMessage('Správa bola úspešne pridaná.');
        $this->redirect('this');
    }

    function handleSave()
    {
        $data = $this->getParam('data');

        $this->dashboard->update($data, $data['id_admin_contact']);
        $this->flashMessage('Správa uložená.');

        $this->invalidateControl();
    }

    function createComponent($name)
    {

        switch ($name) {

            case 'addMessage':
                $f = new Form;

                $f->addTextarea('text', 'Správa');
                $f->addSubmit('btn', 'Pridať')
                    ->setAttribute('class', 'btn btn-success btn-large');;
                $f->onSuccess[] = array($this, 'actionAdd');

                return $f;

                break;
            case 'simpleGrid':

                $query = $this->dashboard->getFluent();

                $grid = new SimpleGrid($query);

                $grid->addColumn('add_date', 'Dátum');
                $grid->addColumn('autor', 'Autor');
                $grid->addColumn('text', 'Text');

                $grid->addColumn('text', 'Text', array(
                    'renderedWithTwoParams' => true,
                    'renderer'              => function ($el, $key) {
                        return '<input type="text" class="simpleGrid-edidable span12" data-id="' . $el['id_dashboard'] . '" data-name="' . $key . '" value="' . $el['text'] . '" />';
                    }
                ));

                $presenter = $this;

                $grid->addAction('delete', array(
                        'class'        => 'btn btn-danger confirm-ajax',
                        'i_class'      => 'icon-trash icon-white',
                        'link_builder' => function ($row) use ($presenter) {
                            return $presenter->link('delete!', array('id' => $row->id_dashboard));
                        })
                );

                return $grid;
                break;

            default:
                return parent::createComponent($name);
                break;
        }
    }

    function createComponentFile($name)
    {

        $f = new FileControl($this, $name);

        $f->setIdFileNode($this->fileNode->getIdFileNode($this->id, 'Gallery'));

        $f->addInput(array('type' => 'text', 'name' => 'name', 'css_class' => 'input-medium', 'placeholder' => 'Sem umiestnite popis'));

        $f->saveDefaultInputTemplate();

        return $f;
    }

}
