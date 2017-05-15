<?php
namespace App\AdminModule\OrderModule\Presenters;

use App\AdminModule\Presenters\AdminPresenter;
use App\Model\Entity\LangModel;
use Nette\Security\AuthenticationException;

abstract class BasePresenter extends AdminPresenter
{

    /** @persistent */
    public $id;

    public static function getPersistentParams()
    {
        return array('id');
    }

    function startup()
    {
        parent::startup();

        if (!$this->user->isAllowed('spravca_eshopu', 'edit')) {
            throw new AuthenticationException('Access denied');
        }
    }

}
