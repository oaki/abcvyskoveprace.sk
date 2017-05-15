<?php
namespace App\AdminModule\ImportManagerModule\Presenters;

use App\AdminModule\Presenters\AdminPresenter;
use App\Model\Entity\GalleryModel;
use Nette\Security\AuthenticationException;

abstract class BasePresenter extends AdminPresenter
{

    function startup()
    {
        parent::startup();

        if (!$this->user->isAllowed('import_manager', 'edit'))
            throw new AuthenticationException('Nedostatočne práva.');

    }

}
