<?php
namespace App\AdminModule\MarkModule\Presenters;

use App\AdminModule\Presenters\AdminPresenter;
use Nette\Security\AuthenticationException;

abstract class BasePresenter extends AdminPresenter
{

    /** @persistent */
    public $id;

    /**
     * @property-read ProductMarkModel $markModel
     */

    protected $markModel;

    function startup()
    {
        parent::startup();

        if (!$this->user->isAllowed('spravca_obsahu', 'edit'))
            throw new AuthenticationException('Nedostatočne práva.');

        $this->markModel = $this->getService('ProductMarkModel');
    }

}
