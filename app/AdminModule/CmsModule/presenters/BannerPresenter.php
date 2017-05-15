<?php

namespace App\AdminModule\CmsModule\Presenters;

use App\Components\Backend\File\FileControl;
use App\Components\SimpleGrid;
use App\Model\Entity\BannerImageModel;
use App\Model\Entity\BannerModel;
use App\Model\Entity\File\FileModel;
use App\Model\Entity\File\FileNodeModel;
use Nette\Application\UI\Form;

class BannerPresenter extends BasePresenter
{

    /**
     * @var \App\Model\Entity\BannerModel $bannerModel
     */
    public $bannerModel;

    /**
     * @var \App\Model\Entity\BannerImageModel $bannerImageModel
     */
    public $bannerImageModel;

    /**
     * @var FileNodeModel
     */
    public $fileNodeModel;

    /**
     * @var FileModel
     */
    public $fileModel;

    public static $types = [
        'big'      => 'Hlavný banner - Veľký',
        'medium'   => 'Hlavný banner - Stredný',
        'small'    => 'Hlavný banner - Malý',
        'treeCols' => 'Banner - tri stĺpce',
        'oneCol'   => 'Banner - spodný'
    ];

    function handleDelete($id)
    {
        $this->bannerModel->delete($id);

        $this['simpleGrid']->redrawControl();
        $this->flashMessage('Zmazané', 'alert-success');
        $this->redrawControl('flashmessage');
//        $this->redirect('default');
    }

    function actionSaveOrder($order = null)
    {
        parse_str($order, $list);

        foreach ($list['banner-id'] as $key => $id) {
            $this->bannerModel->update([
                'ORDER' => $key
            ], $id);
        }

        $this->terminate();
    }

    function renderDefault()
    {
        $this->template->list = $this->bannerModel
            ->getFluent()
            ->orderBy('order')
            ->fetchAll();
    }

    function renderAdd()
    {
        $id = $this->bannerModel->insertAndReturnLastId(['is_active' => '0', 'order' => '1']);
        $this->redirect('edit', ['id' => $id]);
    }

    function renderEdit($id)
    {
        $this['form']->setDefaults($this->bannerModel->fetch($id));
    }

    function createComponentSimpleGrid($name)
    {
        $grid = new SimpleGrid($this->bannerModel->getFluent());

        $fileManager = $this->fileNodeModel;
        $fileModel   = $this->fileModel;

        $types = self::$types;

        $grid->addColumn('id_banner', 'ID');
        $grid->addColumn('name', 'Názov');
        $grid->addColumn('is_active', 'Aktívny');
        $grid->addColumn('type', 'Typ', [
            'renderer' => function ($value) use ($types) {
                return $types[$value];
            }
        ]);
        $grid->addColumn('img', 'Obrázok', array(
            'renderedWithTwoParams' => true,
            'renderer'              => function ($row, $colName) use ($fileManager, $fileModel) {
                $id_file_node = $fileManager->getIdFileNode($row->id_banner, 'Banner');
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

        $presenter = $this;
        $grid->addAction('edit', array(
                'class'        => 'btn',
                'i_class'      => 'icon-pencil',
                'link_builder' => function ($row) use ($presenter) {
                    return $presenter->link('edit', array('id' => $row->id_banner));
                })
        );

        $grid->addAction('delete', array(
                'class'        => 'btn btn-danger confirm-ajax',
                'i_class'      => 'icon-trash icon-white',
                'link_builder' => function ($row) use ($presenter) {
                    return $presenter->link('delete!', array('id' => $row->id_banner));
                })
        );

        return $grid;
    }

    function createComponentFile($name)
    {

        $f = new FileControl($this, $name);

        $f->setIdFileNode($this->getService('FileNode')->getIdFileNode($this->id, 'Banner'));

        $f->addInput([
            'type'        => 'text',
            'name'        => 'name',
            'css_class'   => 'input-medium',
            'placeholder' => 'Nadpis'
        ]);

        $f->addInput([
            'type'        => 'text',
            'name'        => 'description',
            'css_class'   => 'input-medium',
            'placeholder' => 'Popis'
        ]);

        $f->addInput([
            'type'        => 'text',
            'name'        => 'link',
            'css_class'   => 'input-medium',
            'placeholder' => 'Link'
        ]);

        $f->saveDefaultInputTemplate();

        return $f;
    }

    public function injectBannerService(BannerModel $service)
    {
        $this->bannerModel = $service;
    }

    public function injectBannerImageService(BannerImageModel $service)
    {
        $this->bannerImageModel = $service;
    }

    public function injectFileNodeModel(FileNodeModel $service)
    {
        $this->fileNodeModel = $service;
    }

    public function injectFileModel(FileModel $service)
    {
        $this->fileModel = $service;
    }

    protected function createComponentForm($name)
    {
        $f = new Form($this, $name);
        $f->addText('name', 'Názov')
            ->addRule(Form::FILLED, 'Názov musí byť vypplnený');
        $f->addSelect('type', 'Typ', self::$types);
        $f->addCheckbox('is_active', 'Aktívny');

        $f->addSubmit('btn', 'Uložiť');
        $f->addHidden('id_banner');
        $f->onSuccess[] = [$this, 'handleForm'];

        return $f;
    }

    public function handleForm(Form $form)
    {
        $values = $form->getValues();

        $this->bannerModel->update($values, $values['id_banner']);

        $this->flashMessage('Uložené');
        $this->redrawControl('flashmessage');
    }

}
