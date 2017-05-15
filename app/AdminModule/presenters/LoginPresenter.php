<?php

namespace App\AdminModule\Presenters;

use App\Presenters\BasePresenter;

class LoginPresenter extends BasePresenter
{

    /** @persistent */
    public $backlink = '';

    public function renderDefault()
    {

    }

    protected function createComponent($name)
    {
        switch ($name) {
            case 'loginForm':

                $form = new \Nette\Application\UI\Form;
                $form->addText('username', 'Prihlasovacie meno')
                    ->addRule(\Nette\Forms\Form::FILLED, $this->translator->translate('Prihlasovacie meno musí byť vyplnené.'));

                $form->addPassword('password', $this->translator->translate('Heslo') . ':')
                    ->addRule(\Nette\Forms\Form::FILLED, $this->translator->translate('Prihlasovacie heslo musí byť vyplnené.'));

                $form->addSubmit('submit_login', 'Log In');

                $renderer = $form->getRenderer();

                $renderer->wrappers['controls']['container'] = null;

                $renderer->wrappers['pair']['container']    = 'div';
                $renderer->wrappers['label']['container']   = null;
                $renderer->wrappers['control']['container'] = null;

                $form->addProtection($this->translator->translate('Sedenie vypršalo. Proším obnovte prihlasovací formulár a zadajte údaje znovu.'), 1800);

                $form['submit_login']->getControlPrototype()->class = 'btnLogin';

                $form->onSuccess[] = [$this, 'loginFormSubmitted'];

                return $form;

                break;

            default:
                return parent::createComponent($name);
                break;
        }
    }

    public function loginFormSubmitted($form)
    {

        try {
//			$this->user->setExpiration('+ 2 days', FALSE);

            $r = $this->user->login($form['username']->value, $form['password']->value);

            $this->restoreRequest($this->backlink);

            $this->redirect('Homepage:');

        } catch (\Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

    public function actionOut()
    {
        $this->getUser()->logout();
        $this->flashMessage('You have been signed out.');
        $this->redirect('default');
    }
}
