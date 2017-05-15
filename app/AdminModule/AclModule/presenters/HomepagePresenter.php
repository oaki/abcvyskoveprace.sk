<?php
/**
 * GUI for Acl
 *
 * @copyright  Copyright (c) 2010 Tomas Marcanik
 * @package    GUI for Acl
 */
namespace App\AdminModule\AclModule\Presenters;

/**
 * Blank Presenter, which redirects to UsersPresenter
 *
 */
class HomepagePresenter extends BasePresenter
{

    public function startup()
    {
        $this->redirect('Users:Default');
    }
}
