<?php
namespace App\FrontModule\Presenters;

abstract class SecurePresenter extends BasePresenter
{

    function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            $this->flashMessage('Nie ste prihlásený.');

            $this->redirect(':Front:Sign:default');
        }
    }
}