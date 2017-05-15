<?php
namespace App\AdminModule\Presenters;

use App\Presenters\BasePresenter;

abstract class Admin_BasePresenter extends BasePresenter
{

    function startup()
    {
        parent::startup();

        if (isset($_GET['APPLICATION_API_KEY'])) {
            $this->user->login('APPLICATION_API_KEY', $_GET['APPLICATION_API_KEY']);
        }

        if (!$this->user->isLoggedIn()) {
            $backlink = $this->storeRequest();
            $this->redirect(':Admin:Login:default', array('backlink' => $backlink, 'lang' => $this->lang));
        }

        if (!$this->user->isAllowed('backend', 'edit')) {
            $this->flashMessage('Nemáte dostatočné prava!!!');
            $backlink = $this->storeRequest();
            $this->redirect(':Admin:Login:default', array('backlink' => $backlink, 'lang' => $this->lang));
        }

    }

}


