<?php
namespace App\AdminModule\SettingModule\Presenters;

use App\Components\Backend\DataGrid\DataGrid;
use App\Model\Entity\Eshop\PaymentModel;

class PaymentPresenter extends BasePresenter
{

    /**
     * @var PaymentModel $paymentModel
     */
    public $paymentModel;

    public function renderDefault()
    {
        $this->template->list = $this->paymentModel->fetchAll();
    }

    public function handleDeletePayment($id_payment)
    {
        $this->paymentModel->delete($id_payment);
        $this->flashMessage("Platba bola vymazaná", 'success');
        $this->redrawControl('flashmessage');
        $this['paymentGrid']->redrawControl();
    }

    public function createComponentPaymentGrid($name)
    {

        $grid = new DataGrid($this, $name);
        $grid->setPrimaryKey('id_payment');
        $fields = function ($container) {
            $container->addText('name', '');
            $container->addText('description', '');
            $container->addText('price', '');
            $container->addSelect('is_default', 'Predvolený', [0 => 'nie', 1 => 'áno']);
        };

        $grid->addInlineAdd()
            ->onControlAdd[] = $fields;

        $presenter = $this;

        $grid->getInlineAdd()->onSubmit[] = function ($values) use ($presenter) {
            $presenter->flashMessage("Doprava bola pridaná", 'success');
            $presenter->paymentModel->insert($values);
            $presenter->redrawControl('flashmessage');
            $presenter['paymentGrid']->redrawControl();
        };

        $grid->addInlineEdit()
            ->onControlAdd[] = $fields;

        $grid->getInlineEdit()->onSetDefaults[] = function ($container, $item) {
            $container->setDefaults([
                'name'        => $item->name,
                'description' => $item->description,
                'price'       => $item->price,
            ]);
        };

        $grid->getInlineEdit()->onSubmit[] = function ($id, $values) use ($presenter) {
            $presenter->flashMessage("Platba bola pridaná", 'success');
            $presenter->paymentModel->update($values, $id);
            $presenter->redrawControl('flashmessage');
        };

        $grid->setDataSource($this->paymentModel->getFluent());

        $grid->addColumnText('name', 'Názov');
        $grid->addColumnText('description', 'Popis');
        $grid->addColumnNumber('price', 'Cena');

        $grid->addColumnText('is_default', 'Predvolený')
            ->setRenderer(function ($item) {
                return ($item->is_default === 1) ? 'áno' : 'nie';
            });

        $grid->addAction('delete', '', 'deletePayment!')
            ->setIcon('trash')
            ->setTitle('Zmazat')
            ->setClass('btn  btn-xs btn-danger ajax')
            ->setConfirm('Naozaj chcete vymazať %s?', 'name');

        return $grid;
    }

    function handleDeleteDelivery($id)
    {
        $this->paymentModel->delete($id);
        $this['deliveryTabella']->invalidateControl();
    }

    public function injectPaymentModel(PaymentModel $model)
    {
        $this->paymentModel = $model;
    }

}