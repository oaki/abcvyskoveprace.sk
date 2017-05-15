<?php

namespace App\AdminModule\OrderModule\Presenters;

use App\Components\Backend\DataGrid\DataGrid;
use App\Model\Entity\Eshop\OrderModel;
use Nette\Application\UI\Form;

class HomepagePresenter extends BasePresenter
{

    /**
     * @var OrderModel @inject
     */
    public $orderModel;

    public function renderDefault($id)
    {

    }

    public function actionCreateInvoice($id)
    {

        /**
         * @SuperFakturaModel $superFaktura
         */
        $superFaktura = $this->context->createInstance('App\Model\SuperFakturaModel');

        $order = $this->orderModel->getAllInfo($id);

        $result = $superFaktura->createInvoice($order);
        if ($result['success']) {
            $this->redirectUrl($result['link']);
        } else {
            $this->flashMessage($result['error']);
            $this->redirect('default');
        }
    }

    public function renderEdit($id_order)
    {
        $this->template->o = $this->orderModel->getAllInfo($id_order);

        $this['statusForm']->setDefaults($this->template->o);
    }

    public function createComponentStatusForm($name)
    {
        $f = new Form($this, $name);

        $f->addSelect('order_status', 'Stav: ', OrderModel::$states);
        $f->addSubmit('btn_submit', 'Uložiť');
        $f->onSuccess[] = $this->handleStatusForm;

        return $f;
    }

    public function handleStatusForm(Form $form)
    {
        $values = $form->getValues();
        $this->orderModel->update($values, $this->getParam('id_order'));
        $this->flashmessage('Upravené');
        $this->redirect('this');
    }

    public function createComponentOrderGrid($name)
    {

        $grid = new DataGrid($this, $name);
        $grid->setPrimaryKey('id_order');
//        $grid->setSortable();

        $source = $this->orderModel
            ->getFluent()->orderBy('id_order DESC');

        $grid->setDataSource($source);

        $grid->addColumnText('id_order', 'ID');

        $grid->addFilterText('id_order', 'ID');

        $grid->addColumnText('name', 'Meno');

        $grid->addFilterText('name', 'Meno');

        $grid->addColumnText('surname', 'Priezvisko');
        $grid->addFilterText('surname', 'Priezvisko');

        $grid->addColumnText('company_name', 'Firma');
        $grid->addColumnText('phone', 'Tel');
        $grid->addColumnText('city', 'Mesto');
        $grid->addColumnText('add_date', 'Dátum');
        $grid->addColumnText('total_price_with_tax', 'Cena s DPH');
        $grid->addColumnText('payment_status', 'Zapl')
            ->setReplacement([0 => 'nie', 1 => 'áno']);

        $states = OrderModel::$states;
        $grid->addColumnText('order_status', 'Stav')
            ->setReplacement($states)->setFilterSelect(['' => 'All'] + $states);

        $grid->addAction('edit', 'Zobraziť');
        $grid->addAction('delete', 'Vymazať');

        return $grid;
    }

    public function injectOrderModel(OrderModel $model)
    {
        $this->orderModel = $model;
    }

}