<?php
namespace App\AdminModule\SettingModule\Presenters;

use App\Model\Entity\Eshop\DeliveryModel;

class DeliveryPresenter extends BasePresenter
{

    /**
     * @var DeliveryModel $deliveryModel
     */
    public $deliveryModel;

    public function injectDeliveryModel(DeliveryModel $deliveryModel)
    {
        $this->deliveryModel = $deliveryModel;
    }

    public function renderDefault()
    {
        $this->template->list = $this->deliveryModel->getFluent()->fetchAll();
    }

    function handleDeleteDelivery($id_delivery)
    {
        $this->deliveryModel->delete($id_delivery);
        $this->flashMessage('Záznam bol vymazaný.');
        $this['deliveryGrid']->redrawControl();
        $this->redrawControl('flashmessage');
    }

    public function createComponentDeliveryGrid($name)
    {

        $grid = new \App\Components\Backend\DataGrid\DataGrid($this, $name);
        $grid->setPrimaryKey('id_delivery');
        $fields = function ($container) {
            $container->addText('name', '');
            $container->addText('description', '');
            $container->addText('price', '');
            $container->addSelect('default', 'Predvolený', [0 => 'nie', 1 => 'áno']);
        };

        $grid->addInlineAdd()
            ->onControlAdd[] = $fields;

        $presenter = $this;

        $grid->getInlineAdd()->onSubmit[] = function ($values) use ($presenter) {
            $presenter->flashMessage("Doprava bola pridaná", 'success');
            $presenter->deliveryModel->insert($values);
            $presenter->redrawControl('flashmessage');
            $presenter['deliveryGrid']->redrawControl();
        };

        $grid->addInlineEdit()
            ->onControlAdd[] = $fields;

        $grid->getInlineEdit()->onSetDefaults[] = function ($container, $item) {
            $container->setDefaults([
//                'id' => $item->id_delivery,
                'name'        => $item->name,
                'description' => $item->description,
                'price'       => $item->price,
            ]);
        };

        $grid->getInlineEdit()->onSubmit[] = function ($id, $values) use ($presenter) {
            $presenter->flashMessage("Doprava bola pridaná", 'success');
            $presenter->deliveryModel->update($values, $id);
            $presenter->redrawControl('flashmessage');
        };

        $grid->setDataSource($this->deliveryModel->getFluent());

        $grid->addColumnText('name', 'Názov');
        $grid->addColumnText('description', 'Popis');
        $grid->addColumnNumber('price', 'Cena');

        $grid->addColumnText('default', 'Predvolený')
            ->setRenderer(function ($item) {
                return ($item->default === 1) ? 'áno' : 'nie';
            });

        $grid->addAction('delete', '', 'deleteDelivery!')
            ->setIcon('trash')
            ->setTitle('Zmazat')
            ->setClass('btn  btn-xs btn-danger ajax')
            ->setConfirm('Naozaj chcete vymazať %s?', 'name');

        return $grid;
    }

}