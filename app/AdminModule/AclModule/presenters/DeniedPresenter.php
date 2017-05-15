<?php
/**
 * GUI for Acl
 *
 * @copyright  Copyright (c) 2010 Tomas Marcanik
 * @package    GUI for Acl
 */

namespace App\AdminModule\AclModule\Presenters;

/**
 * Presenter for unauthorized access
 *
 */
class DeniedPresenter extends BasePresenter
{

    function startup()
    {
        parent::startup();
        $this->redirect(':Admin:Login:');
    }

}
