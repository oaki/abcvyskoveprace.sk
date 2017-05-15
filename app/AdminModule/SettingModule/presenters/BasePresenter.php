<?php
namespace App\AdminModule\SettingModule\Presenters;

use App\AdminModule\Presenters\Admin_BasePresenter;
use Nette\Security\AuthenticationException;

abstract class BasePresenter extends Admin_BasePresenter
{

    function startup()
    {
        parent::startup();

        if (!$this->user->isAllowed('spravca_obsahu', 'edit')) {
            throw new AuthenticationException("Setting Forbidden");
        }
    }

}