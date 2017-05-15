<?php
namespace App\AdminModule\GalleryModule\Presenters;

use App\Components\Backend\File\FileControl;
use App\Components\SimpleGrid;
use App\Model\Entity\File\FileNodeModel;
use Nette\Application\UI\Form;
use Nette\DI\Container;

/**
 * Description of GalleryPresenter
 *
 * @author oaki
 */
class HomepagePresenter extends Admin_Gallery_BasePresenter
{

    /**
     * @var FileNodeModel @inject
     */
    private $fileNode;

    function renderDefault()
    {

    }

    function renderEdit($id)
    {
        $this->template->info = $this->gallery->fetch($id);

    }

    function handleDelete($id)
    {
        $gallery = $this->gallery->fetch($id);

        $conf = $this->context->parameters['webimages'];

        $idFileNode = $this->fileNode->getIdFileNode($id, 'Gallery');
        $files      = $this->fileNode->getFiles($idFileNode);

        foreach ($files as $file) {
            $this->getService('File')->deleteFile($file->id_file, $conf['uploadDir'], $conf['tempDir']);
        }

        $this->gallery->delete($id);

        $this->flashMessage('Galeria bol úspešne zmazaná.');

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
        $arr    = array(
            'name'     => $values->name,
            'add_date' => new \DibiDateTime(),
            'id_user'  => $this->user->getId()
        );
        $lastId = $this->gallery->insertAndReturnLastId($arr);

        $this->flashMessage('Galéria bola úspešne pridaná.');
        $this->redirect('edit', $lastId);
    }

    function handleSave()
    {

        $data = $this->getParameter('data');
        $id   = $data['id'];
        unset($data['id']);
        $this->gallery->update($data, $id);
        $this->flashMessage('Galéria uložená.');

        $this->redrawControl();
    }

    function createComponent($name)
    {

        switch ($name) {

            case 'addDocument':
                $f = new Form;
//                $f->addUpload('file', 'Vyberte súbor')
//                        ->addRule(Form::FILLED, 'Nezadali ste žiadny súbor.');
                $f->addText('name', 'Názov galérie')
                    ->addRule(Form::FILLED, 'Názov musí byť vyplnený.');

                $f->addSubmit('btn', 'Pridať')
                    ->setAttribute('class', 'btn btn-success');;
                $f->onSuccess[] = array($this, 'actionAdd');

                return $f;

                break;
            case 'simpleGrid':

                $query = $this->gallery
                    ->getFluent('gallery.*, CONCAT(user.name," ",user.surname) AS autor')
                    ->leftJoin('user')->using('(id_user)');

                $grid = new SimpleGrid($query);

                $grid->addColumn('add_date', 'Dátum');
                $grid->addColumn('autor', 'Autor');
                $grid->addColumn('name', 'Názov', array(
                    'renderedWithTwoParams' => true,
                    'renderer'              => function ($el, $key) {
                        return '<input type="text" class="gallery-name span12" data-id="' . $el['id_gallery'] . '" value="' . $el['name'] . '" />';
                    }
                ));

                $presenter = $this;

                $grid->addAction('edit', array(
                        'class'        => 'btn',
                        'i_class'      => 'icon-edit',
                        'title'        => 'Zobraziť',
//                    'text' => 'Zobraziť',
                        'link_builder' => function ($row) use ($presenter) {
                            return $presenter->link('edit', array('id' => $row->id_gallery));
                        })
                );
                $grid->addAction('delete', array(
                        'class'        => 'btn btn-danger confirm-ajax',
                        'i_class'      => 'icon-trash icon-white',
                        'link_builder' => function ($row) use ($presenter) {
                            return $presenter->link('delete!', array('id' => $row->id_gallery));
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

    /**
     * @param FileNodeModel $service
     */
    public function injectPageModel(FileNodeModel $service)
    {
        $this->fileNode = $service;
    }
}
