<?php
namespace App\AdminModule\DashboardModule\Presenters;

use App\AdminModule\Presenters\AdminPresenter;
use Nette\Security\AuthenticationException;

abstract class Admin_Dashboard_BasePresenter extends AdminPresenter
{

    /** @persistent */
    public $id;

    /**
     * @property-read DashboardModel $dashboard
     */

    protected $dashboard;

    function startup()
    {
        parent::startup();

        if (!$this->user->isAllowed('spravca_obsahu', 'edit'))
            throw new AuthenticationException('Nedostatočne práva.');

        $this->dashboard = $this->getService('DashboardModel');
    }

}
