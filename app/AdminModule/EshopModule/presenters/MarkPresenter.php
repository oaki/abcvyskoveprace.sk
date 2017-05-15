<?php

namespace App\AdminModule\EshopModule\Presenters;

use AdminModule\SimpleGrid;
use App\Components\Backend\File\FileControl;
use App\Model\Entity\Eshop\ProductMarkModel;
use App\Model\Entity\File\FileModel;
use EshopModule\Product\ProductMarkForm;

/**
 * Description of MarkPresenter
 *
 * @author oaki
 */

/**
 * @property-read Eshop\MarkModel $markModel
 */
class MarkPresenter extends BasePresenter
{

    protected $markModel;

    /**
     * @var FileModel $fileModel
     */
    protected $fileModel;

    function actionAdd()
    {
        $new_id = $this->markModel->insertAndReturnLastId(array());
        $this->redirect('edit', array('id' => $new_id));
    }

    function renderEdit($id)
    {

    }

    function renderDefault()
    {

    }

    function handleDeleteMark($id)
    {
        $this->markModel->delete($id);
        $this->flashMessage('Značka bola zmazaná');
        $this['simpleGrid']->invalidateControl();
        $this->redrawControl();

    }

    function createComponent($name)
    {

        switch ($name) {

            case 'productMarkForm':
                $m = new \App\AdminModule\EshopModule\Forms\ProductForm\ProductMarkForm($this, $name, $this->markModel);
//                dump($this->markModel->fetch($this->id));
//                exit;
                $m->setDefaults($this->markModel->fetch($this->id));

                return $m;
                break;

            case 'simpleGrid':

                $grid = new \App\Components\SimpleGrid($this->markModel->getFluent());

                $fileManager = $this->getService('FileNode');
                $fileModel   = $this->fileModel;
                $grid->addColumn('id_product_mark', 'ID');
                $grid->addColumn('top', 'TOP');
                $grid->addColumn('img', 'Obrázok', array(
                    'renderedWithTwoParams' => true,
                    'renderer'              => function ($row, $colName) use ($fileManager, $fileModel) {
                        $id_file_node = $fileManager->getIdFileNode($row->id_product_mark, 'Mark');
                        $files        = $fileManager->getFiles($id_file_node);

                        $file = current($files);

                        if (!$file) {
                            $file        = [];
                            $file['src'] = 'no-image';
                            $file['ext'] = 'jpg';
                        }
                        $img = $fileModel->getURL($file['src'], $file['ext'], 80, 80, 5);

                        return '<img src="' . $img . '"/>';
                    }
                ));

                $grid->addColumn('name', 'Názov');

                $presenter = $this;
                $grid->addAction('edit', array(
                        'class'        => 'btn',
                        'i_class'      => 'icon-pencil',
                        'link_builder' => function ($row) use ($presenter) {
                            return $presenter->link('edit', array('id' => $row->id_product_mark));
                        })
                );

                $grid->addAction('delete', array(
                        'class'        => 'btn btn-danger confirm-ajax',
                        'i_class'      => 'icon-trash icon-white',
                        'link_builder' => function ($row) use ($presenter) {
                            return $presenter->link('deleteMark!', array('id' => $row->id_product_mark));
                        })
                );

                return $grid;
                break;

            default :
                return parent::createComponent($name);
                break;
        }
    }

    function createComponentFile($name)
    {

        $f = new FileControl($this, $name);

        $f->setIdFileNode($this->getService('FileNode')->getIdFileNode($this->id, 'Mark'));

        $f->addInput(array('type' => 'text', 'name' => 'name', 'css_class' => 'input-medium', 'placeholder' => 'Sem umiestnite popis'));

        $f->saveDefaultInputTemplate();

        return $f;
    }

    public function injectProductMark(ProductMarkModel $model)
    {
        $this->markModel = $model;
    }

    public function injectFileModel(FileModel $model)
    {
        $this->fileModel = $model;
    }
}
