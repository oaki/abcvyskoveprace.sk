<?php
namespace App\AdminModule\MarkModule\Presenters;

use App\Components\Backend\File\FileControl;
use App\Components\SimpleGrid;
use App\Model\Entity\File\FileNodeModel;
use Nette\Application\UI\Form;

class HomepagePresenter extends BasePresenter
{

    private $fileNode;

    function renderDefault()
    {

    }

    function handleDelete($id)
    {

        $this->markModel->delete($id);

        $this->flashMessage('Správa bol úspešne zmazaná.');

        if ($this->isAjax()) {
            $this->redrawControl('flashmessage');
            $this['simpleGrid']->invalidateControl();
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

        $this->markModel->insert($arr);

        $this->flashMessage('Správa bola úspešne pridaná.');
        $this->redirect('this');
    }

    function handleSave()
    {
        $data = $this->getParameter('data');

        $this->markModel->update($data, $data['id_admin_contact']);
        $this->flashMessage('Správa uložená.');

        $this->redrawControl();
    }

    function createComponent($name)
    {

        switch ($name) {

            case 'markGrid':

                $query = $this->markModel->getFluent();

                $grid = new SimpleGrid($query);

                $grid->addColumn('add_date', 'Dátum');
                $grid->addColumn('autor', 'Autor');
                $grid->addColumn('text', 'Text');

                $presenter = $this;

                $grid->addAction('delete', array(
                        'class'        => 'btn btn-danger confirm-ajax',
                        'i_class'      => 'icon-trash icon-white',
                        'link_builder' => function ($row) use ($presenter) {
                            return $presenter->link('delete!', array('id' => $row->id_product_mark));
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

        $f->setIdFileNode($this->fileNode->getIdFileNode($this->id, 'ProductMark'));

        $f->addInput(array('type' => 'text', 'name' => 'name', 'css_class' => 'input-medium', 'placeholder' => 'Sem umiestnite popis'));

        $f->saveDefaultInputTemplate();

        return $f;
    }

    public function injectFileNode(FileNodeModel $model)
    {
        $this->fileNode = $model;
    }

}
