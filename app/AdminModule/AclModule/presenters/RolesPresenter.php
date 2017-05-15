<?php

/**
 * GUI Acl bootstrap file.
 *
 * @copyright  Copyright (c) 2010 Tomas Marcanik
 * @package    GUI Acl
 */

namespace App\AdminModule\AclModule\Presenters;

use App\AdminModule\AclModule\Models\RolesModel;
use Nette\Application\UI\Form;

/**
 * Roles
 *
 */
class RolesPresenter extends BasePresenter
{

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
        $nodes                   = new RolesModel($this->db);
        $this->template->nodes   = $nodes;
        $this->template->parents = $nodes->getChildNodes(null);
    }

    /******************
     * Add and Edit
     ******************/
    public function actionAdd($id = 0)
    { // id = parent id
        $form = $this->getComponent('addEdit');
        $form->setDefaults(array('parent_id' => $id));
    }

    public function actionEdit($id)
    {
        $sql  = $this->db->query('SELECT key_name, parent_id, name, comment FROM [acl_roles] WHERE id=%i;', $id);
        $form = $this->getComponent('addEdit');
        if (count($sql)) {
            $row = $sql->fetch();
            if (empty($row->parent_id))
                $row->parent_id = 0;
            $form->setDefaults($row);
        } else
            $form->addError('This role does not exist.');
    }

    protected function createComponentAddEdit($name)
    {
        $roles[0] = ' ';
        $mroles   = new RolesModel($this->db);
        $rows     = $mroles->getTreeValues();
        foreach ($rows as $key => $row) { // function array_merge does't work correctly with integer indexes
            // manual array merge
            $roles[$key] = $row;
        }

        $form                                  = new Form($this, $name);
        $renderer                              = $form->getRenderer();
        $renderer->wrappers['label']['suffix'] = ':';
        //$form->addGroup('Add');
        $form->addText('name', 'Name', 30)
            ->addRule(Form::FILLED, 'You have to fill name.')
            ->getControlPrototype()->onChange("create_key()");
        $form->addText('key_name', 'Key', 30)
            ->addRule(Form::FILLED, 'You have to fill key.');
        $form->addSelect('parent_id', 'Parent', $roles, 15);
        $form->addTextArea('comment', 'Comment', 40, 4)
            ->setRequired()
            ->addRule(Form::MAX_LENGTH, 'Comment must be at least %d characters.', 250);
        if ($this->getAction() == 'add')
            $form->addSubmit('add', 'Add');
        else
            $form->addSubmit('edit', 'Edit');
        $form->onSuccess[] = array($this, 'addEditOnFormSubmitted');
    }

    public function addEditOnFormSubmitted(Form $form)
    {
        // add action
        if ($this->getAction() == 'add') {
            try {
                $values = $form->getValues();
                if ($values['parent_id'] == 0)
                    $values['parent_id'] = null;
                $this->db->query('INSERT INTO [acl_roles] %v;', $values);
                $this->flashMessage('The role has been added.', 'ok');

                unset($this->cache['acl']); // invalidate cache

                $this->redirect('Roles:');
            } catch (\Exception $e) {
                $form->addError('The role has not been added.');
                throw $e;
            }
        } else { // edit action
            try {
                $id     = $this->getParam('id');
                $values = $form->getValues();
                if ($values['parent_id'] == 0)
                    $values['parent_id'] = null;
                $this->db->query('UPDATE [acl_roles] SET %a WHERE id=%i;', $values, $id);
                $this->flashMessage('The role has been edited.', 'ok');
                unset($this->cache['acl']); // invalidate cache
                $this->redirect('Roles:');
            } catch (\Exception $e) {
                $form->addError('The role has not been edited.');
                throw $e;
            }
        }
    }

    /******************
     * Delete
     ******************/
    public function actionDelete($id)
    {
        $sql = $this->db->query('SELECT name FROM [acl_roles] WHERE id=%i;', $id);
        if (count($sql)) {
            $this->template->role = $sql->fetchSingle();
        } else {
            $this->flashMessage('This role does not exist.');
            $this->redirect('Roles:');
        }
    }

    protected function createComponentDelete($name)
    {
        $form = new Form($this, $name);
        $form->addSubmit('delete', 'Delete');
        $form->addSubmit('cancel', 'Cancel');
        $form->onSuccess[] = array($this, 'deleteOnFormSubmitted');
    }

    public function deleteOnFormSubmitted(Form $form)
    {
        if ($form['delete']->isSubmittedBy()) {
            try {
                $id = $this->getParam('id');
                $this->db->query('DELETE FROM [acl_roles] WHERE id=%i;', $id);
                $this->flashMessage('The role has been deleted.', 'ok');
                unset($this->cache['acl']); // invalidate cache
                $this->redirect('Roles:');
            } catch (\Exception $e) {
                $form->addError('The role has not been deleted.');
                throw $e;
            }
        } else
            $this->redirect('Roles:');
    }

    /******************
     * Users
     ******************/
    public function actionUsers($id)
    {
        $nodes                   = new RolesModel($this->db);
        $this->template->nodes   = $nodes;
        $this->template->parents = $nodes->getChildNodes(null);

        $this->template->role = $this->db->fetchSingle('SELECT name FROM [acl_roles] WHERE id=%i;', $id);

        $sql                   = $this->db->query('SELECT u.id_user, u.login FROM [user] AS u
                                LEFT JOIN [user_roles] AS r ON u.id_user=r.user_id
                                WHERE r.role_id=%i', $id,
            'ORDER BY u.login');
        $users                 = $sql->fetchAll();
        $this->template->users = $users;
    }

    /******************
     * Access
     ******************/
    public function actionAccess($id)
    {
        $nodes                   = new RolesModel($this->db);
        $this->template->nodes   = $nodes;
        $this->template->parents = $nodes->getChildNodes(null);

        $role                 = $this->db->fetch('SELECT key_name, name FROM [acl_roles] WHERE id=%i;', $id);
        $this->template->role = $role->name;

        $access                 = new AccessModel(array($role), $this->context->connection);
        $this->template->access = $access->getAccess();
    }
}
