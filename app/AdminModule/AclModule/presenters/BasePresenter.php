<?php
namespace App\AdminModule\AclModule\Presenters;

use App\AdminModule\Presenters\Admin_BasePresenter;

/**
 * Description of BasePresenter
 *
 * @author oaki
 */
abstract class BasePresenter extends Admin_BasePresenter
{

    public $cache;

    public $db;

    function beforeRender()
    {
        parent::beforeRender();
        $this->template->current = $this->getPresenter()->getName();
    }

    /**
     * Check if the user has permissions to enter this section.
     * If not, then it is redirected.
     *
     */
    public function checkAccess()
    {

        // if the user is not allowed access to the ACL, then redirect him
//		echo ACL_RESOURCE;
//		echo '<br >'.ACL_PRIVILEGE;exit;
        if (!$this->user->isAllowed('acl', 'edit')) {
            // @todo change redirect to login page
            $this->redirect('Denied:');
        }
    }

    public function injectDbConnection(\DibiConnection $db)
    {
        $this->db = $db;
    }

}