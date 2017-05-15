<?php
namespace App\FrontModule\Components;

use App\Components\BaseControl;
use App\FrontModule\Forms\BaseForm;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;

class LoginControl extends BaseControl
{

    function render()
    {
        $template = $this->template;
        $template->setFile(dirname(__FILE__) . '/default.latte');
        $this['loginForm']->setDefaults(['remember_me' => true]);
        $template->render();
    }

    protected function createComponentLoginForm($name)
    {
        $f = new BaseForm($this, $name);
        $f->addText('email', 'Prihlasovacie meno')
            ->addRule(Form::FILLED, 'Prihlasovacie meno musí byť vyplnené.');

        $f->addPassword('password', 'Heslo')
            ->addRule(Form::FILLED, 'Heslo musí byť vyplnené.');

        $f->addCheckbox('rememberme', 'pamätať si prihlásenie 14 dní');

        $f->addSubmit('login', 'Prihlásiť sa');

        $f->onSuccess[] = array($this, 'processForm');

        return $f;
    }

    public function processForm(Form $form)
    {
        $values = $form->getValues();
        $user   = $this->getPresenter()->getUser();
        if ($values->rememberme) {
            $user->setExpiration('14 days', false);
        } else {
            $user->setExpiration('20 minutes', true);
        }

        try {
            $user->login($values->email, $values->password);
            $this->getPresenter()->orderSummary->updateCartPricesFromDb();
            $this->getPresenter()->flashMessage('Boli ste úspenšne prihlásený.');
            $this->getPresenter()->redirect('this');
        } catch (AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

}