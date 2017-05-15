<?php
namespace App\AdminModule\Presenters;

class HomepagePresenter extends Admin_BasePresenter
{

    function startup()
    {
        $this->redirect(':Admin:Cms:Homepage:default');
    }
}
