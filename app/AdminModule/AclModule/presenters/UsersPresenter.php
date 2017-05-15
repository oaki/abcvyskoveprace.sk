<?php

/**
 * GUI for Acl
 *
 * @copyright  Copyright (c) 2010 Tomas Marcanik
 * @package    GUI for Acl
 */
namespace App\AdminModule\AclModule\Presenters;

use App\AdminModule\AclModule\Models\AccessModel;
use App\AdminModule\AclModule\Models\RolesModel;
use Nette\Application\UI\Form;

/**
 * Presenter for user management
 *
 */
class UsersPresenter extends BasePresenter
{

    /** @var string */
    private $search = '';

    /**
     * Init method
     */
    public function startup()
    {
        parent::startup();
        $this->checkAccess();

    }

    /******************
     * Default
     ******************/
    public function renderDefault()
    {
        $form                 = $this->getComponent('search');
        $this->template->form = $form;
        $users_roles          = array();

        // paginator
//        $vp = $this['visualPaginator'];
//        $paginator = $vp->getPaginator();
//        $paginator->itemsPerPage = 20;

        $sql = $this->context->getService('connection')->query('SELECT id_user, login FROM [user] ' . (!empty($this->search) ? 'WHERE login LIKE %s ' : '') . 'ORDER BY login;', $this->search);
//        $paginator->itemCount = count($sql);
        if (!empty($this->search)) { // disable paginator
//            $paginator->itemsPerPage = $paginator->itemCount;
        }
//        $users = $sql->fetchAll($paginator->offset, $paginator->itemsPerPage);
        $users = $sql->fetchAll();
        foreach ($users as $user) {
            $users_roles[$user->id_user]['login'] = $user->login;
            $sql2                                 = $this->db->query('SELECT r.id, r.name
                                    FROM [acl_roles] AS r
                                    JOIN [user_roles] AS u ON r.id=u.role_id
                                    WHERE u.user_id=%i
                                    ORDER BY r.name;', $user->id_user);
            $roles                                = $sql2->fetchAll();
            $users_roles[$user->id_user]['roles'] = array();
            foreach ($roles as $role) {
                $users_roles[$user->id_user]['roles'][$role->id] = $role->name;
            }
        }

        $this->template->users = $users_roles;
    }

    protected function createComponentSearch($login)
    {
        $form = new Form($this, $login);
        //$form->addGroup('Search');
        $form->addText('login', 'login:', 30)
            ->addRule(Form::FILLED, 'You have to fill login.');
        $form->addSubmit('search', 'Search');
        $form->onSuccess[] = array($this, 'searchOnFormSubmitted');
    }

    public function searchOnFormSubmitted(Form $form)
    {
        $values       = $form->getValues();
        $this->search = strtr($values['login'], "*", "%");
    }

    /******************
     * Add and Edit
     ******************/
    public function actionAdd()
    {

    }

    public function actionEdit($id)
    {
        $sql  = $this->db->query('SELECT login FROM [user] WHERE id_user=%i;', $id);
        $form = $this->getComponent('addEdit');
        if (count($sql)) {
            $login = $sql->fetchSingle();
            $sql   = $this->db->query('SELECT role_id AS roles FROM [user_roles] WHERE user_id=%i;', $id);
            $roles = $sql->fetchPairs();
            $form->setDefaults(array('login' => $login, 'roles' => $roles));
        } else
            $form->addError('This user does not exist.');
    }

    protected function createComponentAddEdit($login)
    {
        $mroles = new RolesModel($this->db);
        $roles  = $mroles->getTreeValues();

        $form                                  = new Form($this, $login);
        $renderer                              = $form->getRenderer();
        $renderer->wrappers['label']['suffix'] = ':';
        //$form->addGroup('Add');
        $form->addText('login', 'login', 30)
            ->addRule(Form::FILLED, 'You have to fill login.');
        $form->addSelect('activate', 'Aktivovany', array('1' => 'ano', 0 => 'nie'));

//        if ($this->getAction()=='add') {
        $form->addPassword('password', 'Password', 30);
//            $form->addPassword('password2', 'Reenter password', 30)
//					->addConditionOn($form['password'], Form::F)
//                ->addRule(Form::FILLED, 'Reenter your password.')
//                ->addRule(Form::EQUAL, 'Passwords do not match.', $form['password']);
//        }
        $form->addMultiSelect('roles', 'Roles', $roles, 15);
        if ($this->getAction() == 'add')
            $form->addSubmit('add', 'Add');
        else
            $form->addSubmit('edit', 'Edit');
        $form->onSuccess[] = array($this, 'addEditOnFormSubmitted');
    }

    public function addEditOnFormSubmitted(Form $form)
    {
        $error = false;
        $this->db->begin();
        // add action
        if ($this->getAction() == 'add') {
            try {
                $values = $form->getValues();
                $roles  = $values['roles'];
                unset($values['roles']);
                if ($values['password'] == '')
                    unset($values['password']);
//                unset($values['password2'], $values['roles']);

//				$values['password'] = md5($values['password']);
//                $this->db->query('INSERT INTO [user] %v;', $values);

                $user_id = $this->getService('User')->insertAndReturnLastId($values);

                if (count($roles)) {
                    foreach ($roles as $role) {
                        $this->db->query('INSERT INTO [user_roles] (user_id, role_id) VALUES (%i, %i);', $user_id, $role);
                    }
                }
                $this->flashMessage('The user has been added.', 'ok');
                $this->db->commit();
                unset($this->cache['acl']); // invalidate cache
                $this->redirect('Users:');
            } catch (\Exception $e) {
                $error = true;
                $form->addError('The user has not been added.');
                throw $e;
            }
        } else { // edit action
            $id = $this->getParam('id');
            try {
                $values = $form->getValues();
                $roles  = $values['roles'];
                unset($values['roles']);
//				unset($values['password2']);
                if ($values['password'] == '')
                    unset($values['password']);
//                $this->db->query('UPDATE [user] SET %a WHERE id=%i;', $values, $id);
                $this->getService('User')->update($values, $id);

                $this->db->query('DELETE FROM [user_roles] WHERE user_id=%i;', $id);
                if (count($roles)) {
                    foreach ($roles as $role) {
                        $this->db->query('INSERT INTO [user_roles] (user_id, role_id) VALUES (%i, %i);', $id, $role);
                    }
                }
                $this->flashMessage('The user has been edited.', 'ok');
                $this->db->commit();
                unset($this->cache['acl']); // invalidate cache
                $this->redirect('Users:');
            } catch (\Exception $e) {
                $error = true;
                $form->addError('The user has not been edited.');
                throw $e;
            }
        }

        if ($error)
            $this->db->rollback();
    }

    /******************
     * Delete
     ******************/
    public function actionDelete($id)
    {
        $sql = $this->db->query('SELECT login FROM [user] WHERE id_user=%i;', $id);
        if (count($sql)) {
            $this->template->user_name = $sql->fetchSingle();
        } else {
            $this->flashMessage('This user does not exist.');
            $this->redirect('Users:');
        }
    }

    protected function createComponentDelete($login)
    {
        $form = new Form($this, $login);
        $form->addSubmit('delete', 'Delete');
        $form->addSubmit('cancel', 'Cancel');
        $form->onSuccess[] = array($this, 'deleteOnFormSubmitted');
    }

    public function deleteOnFormSubmitted(Form $form)
    {
        if ($form['delete']->isSubmittedBy()) {
            try {
                $id = $this->getParam('id');
                $this->db->query('DELETE FROM [user] WHERE id_user=%i;', $id);
                $this->flashMessage('The user has been deleted.', 'ok');
                unset($this->cache['acl']); // invalidate cache
                $this->redirect('Users:');
            } catch (\Exception $e) {
                $form->addError('The user has not been deleted.');
                throw $e;
            }
        } else
            $this->redirect('Users:');
    }

    /******************
     * Access
     ******************/
    public function actionAccess($id)
    {
        $nodes                   = new RolesModel($this->db);
        $this->template->nodes   = $nodes;
        $this->template->parents = $nodes->getChildNodes(null);

        $user                      = $this->db->fetchSingle('SELECT login FROM [user] WHERE id_user=%i;', $id);
        $this->template->user_name = $user;

        $roles = $this->db->fetchAll('SELECT r.key_name FROM [acl_roles] AS r
                                    RIGHT JOIN [user_roles] AS ur ON r.id=ur.role_id
                                    WHERE ur.user_id=%i;', $id);

        $access                 = new AccessModel($roles, $this->db);
        $this->template->access = $access->getAccess();
    }

}
