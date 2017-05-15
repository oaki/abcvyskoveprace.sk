<?php
namespace App\FrontModule\Presenters;

use App\FrontModule\components\OrderSummaryControl\OrderSummary;
use App\FrontModule\components\OrderSummaryControl\OrderSummaryControl;

use App\FrontModule\Forms\BaseForm;
use App\FrontModule\Forms\RegistrationFormFactory;
use App\Services\Mail;
use Nette\Application\UI\Form;

class CartPresenter extends BasePresenter
{

    /**
     * @var RegistrationFormFactory @inject
     */
    public $registrationFormFactory;

    /**
     * @var Mail @inject
     */
    public $mailer;

    public function renderDefault()
    {

    }

    public function renderStep1()
    {
        $this->orderSummary->setStep(2);
        $this->redirect('step2');
    }

    public function renderStep2()
    {
        $this->check();

        $shippingModel = $this->orderSummary->getShipping();

        $this->template->shippings     = $shippingModel->getShippingOptions();
        $this->template->shippingPrice = $shippingModel->getShippingPrice($this->orderSummary['shipping']);
    }

    public function renderStep3()
    {
        $this->check();
    }

    public function renderStep4()
    {
        $this->check();

        $this->template->payments = $this->orderSummary->getPayment()->getPaymentOptions();

    }

    public function renderStep5()
    {
        $this->check();

        $this->template->userInfo = $this->orderSummary->getUserData()->getValues();
    }

    function handleDelete($key)
    {
        $this['cart']->handleDelete($key);
        $this->redrawControl();
    }

    public function handleOrderSummary(array $groups)
    {
        $this['orderSummary']->redrawControl();
    }

    public function processRegistrationForm(Form $form)
    {
        $values = (array)$form->getValues();
        $this->orderSummary->getUserData()->setValues($values);

        $this->orderSummary->setStep(4);

        $this->redirect('step4');
    }

    public function handleOrderForm(Form $form)
    {
        $values = $form->getValues(true);

        $this->orderSummary->update([
            'comment' => $values['comment']
        ]);

        $this->processOrder();

        $this->flashMessage('Vaša objednávka bola odoslaná.');
        $this->redirect('success');

    }

    private function processOrder()
    {

        $values   = $this->orderSummary->getUserData()->getValuesAsArray();
        $shipping = $this->orderSummary->getCheckedShipping();
        $payment  = $this->orderSummary->getCheckedPayment();

        $values['total_price']          = $this->orderSummary->getTotalPrice();
        $values['total_price_with_tax'] = $this->orderSummary->getTotalPriceWithVat();

        $values['delivery_title'] = $shipping['name'];
        $values['delivery_price'] = $shipping['price']->price;
        $values['delivery_tax']   = $this->getService('SettingModel')->getValueByName('DELIVERY_TAX');

        $values['payment_title'] = $payment['name'];
        $values['payment_price'] = $payment['price'];
        $values['payment_tax']   = $this->getService('SettingModel')->getValueByName('PAYMENT_TAX');

        $values['email'] = $values['login'];
        unset($values['login']);
        unset($values['password']);
        unset($values['passwordCheck']);

        if ($this->user->isLoggedIn()) {
            $values['id_user'] = $this->user->getId();
        }

        $id_order          = $this->context->getService('OrderModel')->insertAndReturnLastId($values);
        $orderProductModel = $this->context->getService('OrderProductModel');

        foreach ($this->cart->getItems() as $i) {
            $orderProductModel->insert([
                'id_order'         => $id_order,
                'id_product_param' => $i->getId(),
                'name'             => $i->getName(),
                'price'            => $i->getPriceModel()->price,
                'price_with_tax'   => $i->getPriceModel()->priceWithTax,
                'count'            => $i->getQuantity(),
                'tax'              => $i->getPriceModel()->tax
            ]);
        }

        $this->sendEmailByOrder($id_order);

        $this->orderSummary->reset();

        $this->redirect('success');

    }

    private function sendEmailByOrder($id_order)
    {
        $template = $this->template;

        $template->o = $order = $this->context->getService('OrderModel')->getAllInfo($id_order);

        $template->footer = $this->getService('SettingModel')->getValueByName('footer_for_emails');
        $this->mailer
            ->setSubject('Objednávka č. ' . $id_order)
            ->setTo($order->email)
            ->setBcc('pavolbincik@gmail.com')
            ->setTemplate(clone $this->template)
            ->setTemplateFile(dirname(__FILE__) . '/templates/Order/OrderEmail.latte')
            ->send();
    }

    protected function createComponentCartForm($name)
    {
        $form     = new BaseForm($this, $name);
        $products = $form->addContainer('products');
        foreach ($this->cart->getItems() as $key => $item) {
            $products->addText($key)
                ->setDefaultValue($item->getQuantity())
                ->setRequired();
        }

        $form->addTextArea('comment', 'Poznámka')
            ->setDefaultValue($this->orderSummary['comment'])
            ->setAttribute('placeholder', 'Poznámka');

        $form->addSubmit('updateBtn', 'Prepočítať')
            ->onClick[] = function ($submit) {
            $this->processForm($submit->form);

//            if ($this->isAjax()) {
            $this['cart']->redrawControl();
            $this['orderSummary']->redrawControl();
            $this->redrawControl();
//            } else {
            $this->redirect('this');
//            }
        };

        $form->addSubmit('next', 'Pokračovať')->onClick[] = function ($submit) {
            $this->processForm($submit->form);
            $this->redirect('step1');
        };

        return $form;
    }

    public function processForm(Form $form)
    {
        $values = $form->values;

        foreach ($values['products'] as $key => $quantity) {
            $this->cart->update($key, (int)$quantity);
        };

        $this->orderSummary->update([
            'comment' => $values->comment
        ]);
    }

    protected function createComponentShippingForm($name)
    {
        $form = new BaseForm($this, $name);

        $form->addRadioList('shipping', 'Doprava', $this->orderSummary->getShipping()->getShippingOptions(true))
            ->setRequired();

        $form->addSubmit('next', 'Pokračovať');

        $defaults = ['shipping' => $this->orderSummary->getCheckedShippingKey()];

        if ($defaults['shipping']) {
            $form->setDefaults($defaults);
        }

        $form->onSuccess[] = $this->handleShippingForm;

        return $form;
    }

    public function handleShippingForm(Form $form)
    {
        $values = $form->getValues();

        $this->orderSummary->update([
            'shipping' => $values->shipping
        ]);

        $this->orderSummary->setStep(3);
        $this->redirect('step3');
    }

    protected function createComponentPaymentForm($name)
    {
        $form = new BaseForm($this, $name);

        $form->addRadioList('payment', 'Platba', $this->orderSummary->getPayment()->getPaymentOptions(true))
            ->setRequired();

        $form->addSubmit('next', 'Pokračovať');

        $defaults = ['payment' => $this->orderSummary->getCheckedPaymentKey()];

        $form->setDefaults($defaults);

        $form->onSuccess[] = $this->handlePaymentForm;

        return $form;
    }

    public function handlePaymentForm(Form $form)
    {
        $values = $form->getValues();

        $this->orderSummary->update([
            'payment' => $values->payment
        ]);

        $this->orderSummary->setStep(5);
        $this->redirect('step5');
    }

    protected function createComponentRegistrationForm($name)
    {

        $f = $this->registrationFormFactory->create($this, $name);

        $f->addSubmit('submit', 'Pokračovať');

        $f['login']->caption = 'Email';

        $f->onSuccess[] = $this->processRegistrationForm;

        if ($this->user->isLoggedIn()) {
            $f->setDefaults($this->user->getIdentity()->data);
        }

        $f->setDefaults($this->orderSummary->getUserData()->getValuesAsArray());

        return $f;
    }

    protected function createComponentOrderForm($name)
    {
        $form = new BaseForm($this, $name);

        $form->addTextArea('comment', 'Poznámka')
            ->setDefaultValue($this->orderSummary['comment'])
            ->setAttribute('placeholder', 'Poznámka');

        $form->addSubmit('next', 'Dokončiť objednávku');

        $form->onSuccess[] = $this->handleOrderForm;

        return $form;
    }

    private function check()
    {
        if ($this->getAction() !== 'default') {
            $hasError = false;
            $counter  = 0;
            foreach ($this->cart->getItems() as $i) {
                $counter++;
                if ($i->hasMsg()) {
                    $hasError = true;
                    break;
                }
            };

            if ($hasError OR $counter === 0) {
                $this->redirect('default');
            }

            $action = $this->getAction();
            $step   = $this->orderSummary->getStep();

            $action = intval(str_replace('step', '', $action));
            if ($action > $step) {
                if ($step < 1) {
                    $step = 1;
                }
                $this->redirect('step' . $step);
            }
        }
    }

    public function createComponentOrderSummary()
    {
        //bocny panel
        return new OrderSummaryControl($this->orderSummary);
    }

}