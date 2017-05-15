<?php
namespace App\AdminModule\Presenters;

abstract class AdminPresenter extends Admin_BasePresenter
{

    public function getService($name)
    {
        return $this->context->getService($name);
    }
}