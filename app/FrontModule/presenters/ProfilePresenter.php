<?php
namespace App\FrontModule\Presenters;

use App\FrontModule\Forms\RegistrationFormFactory;
use App\Model\Entity\Eshop\OrderModel;
use App\Model\Entity\UserModel;
use Nette\Application\UI\Form;

class ProfilePresenter extends SecurePresenter
{

    /**
     * @var RegistrationFormFactory @inject
     */
    public $registrationFormFactory;

    /**
     * @var OrderModel @inject
     */
    public $orderModel;

    /**
     * @var UserModel @inject
     */
    public $userModel;

    public function renderDefault()
    {

    }

    public function renderEdit()
    {

    }

    public function renderOrderList()
    {
        $this->template->orderStates = OrderModel::$states;
        $this->template->orderList   = $this->orderModel
            ->getFluent()
            ->where('id_user = %i', $this->user->getIdentity()->getId())
            ->where('deleted = 0')
            ->fetchAll();
    }

    public function renderOrder($id)
    {
        //check if order belong to him
        $id_order = $this->orderModel->getFluent('id_order')->where('id_user = %i', $this->user->getId(), 'AND id_order = %i', $id)->fetchSingle();
        if ($id_order) {
            $this->template->o = $this->orderModel->getAllInfo($id_order);
        } else {
            $this->flashMessage('Objednávka neexistuje');
            $this->redirect('default');
        }

    }

    public function renderInvoice()
    {
        $this->template->invoiceList = $this->orderModel->getInvoices($this->user->getIdentity()->data['REPRE_ID']);
    }

    protected function createComponentUserForm($name)
    {
        $f = $this->registrationFormFactory->create($this, $name);
        $f->addSubmit('submit', 'Uložiť');

        $this->doBootstrapForm($f);
        $f->onSuccess[] = $this->processUserForm;

        $f->setDefaults($this->userModel->fetch($this->user->getId()));

        return $f;
    }

    public function processUserForm(Form $form)
    {
        $values = $form->getValues();

        if ($values['password'] == '') {
            unset($values['password']);
        }

        unset($values['passwordCheck']);

        $this->userModel->update($values, $this->user->getId());
        $this->flashMessage("Použivateľ bol úspešne upravený.");
        $this->redirect('this');
    }

    private function doBootstrapForm(&$f)
    {
        $renderer                                        = $f->getRenderer();
        $renderer->wrappers['controls']['container']     = null;
        $renderer->wrappers['pair']['container']         = 'div class=form-group';
        $renderer->wrappers['pair']['.error']            = 'has-error';
        $renderer->wrappers['control']['container']      = 'div class=col-sm-9';
        $renderer->wrappers['label']['container']        = 'div class="col-sm-3 control-label"';
        $renderer->wrappers['control']['description']    = 'span class=help-block';
        $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
// make form and controls compatible with Twitter Bootstrap
        $f->getElementPrototype()->class('form-horizontal');

        foreach ($f->getControls() as $control) {
            $type = $control->getOption('type');
            if ($type === 'button') {
                $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
                $usedPrimary = true;
            } elseif (in_array($type, ['text', 'textarea', 'select'], true)) {
                $control->getControlPrototype()->addClass('form-control');
            } elseif (in_array($type, ['checkbox', 'radio'], true)) {
                $control->getSeparatorPrototype()->setName('div')->addClass($type);
            }
        }

    }

}