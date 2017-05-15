<?php
/**
 * GUI Acl
 *
 * @copyright  Copyright (c) 2010 Tomas Marcanik
 * @package    GUI Acl
 */

namespace App\AdminModule\AclModule\Presenters;

use App\AdminModule\AclModule\Models\ResourcesModel;
use App\AdminModule\AclModule\Models\RolesModel;
use Nette\Application\UI\Form;
use Nette\Neon\Exception;

/**
 * Permission
 *
 */
class PermissionPresenter extends BasePresenter
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
        // paginator
//        $vp = $this['visualPaginator'];
//        $paginator = $vp->getPaginator();
//        $paginator->itemsPerPage = 20;

        $sql = $this->db->query('SELECT a.id, a.access, ro.name AS role, re.name AS resource, p.name AS privilege
                                FROM [acl] AS a
                                LEFT JOIN [acl_roles] AS ro ON a.role_id=ro.id
                                LEFT JOIN [acl_resources] AS re ON a.resource_id=re.id
                                LEFT JOIN [acl_privileges] AS p ON a.privilege_id=p.id
                                ORDER BY ro.name;');
        $sql->setType('access', 'b');
//        $paginator->itemCount = count($sql);
        $acl = $sql->fetchAll();
//        $acl = $sql->fetchAll($paginator->offset, $paginator->itemsPerPage);
        $this->template->acl = $acl;
    }

    /******************
     * Add and Edit
     ******************/
    public function actionAdd($id)
    {
        $form                 = $this->getComponent('addEdit');
        $this->template->form = $form;
    }

    public function actionEdit($id)
    {
        $sql = $this->db->query('SELECT * FROM [acl] WHERE id=%i;', $id);

        if (count($sql)) {
            $form = $this->getComponent('addEdit');
            $sql->setType('access', 'b');
            $row         = $sql->fetch();
            $row->access = (int)$row->access;
            $form->setDefaults($row);
            $this->template->form = $form;
        } else {
            $this->flashMessage('This acces does not exist.');
            $this->redirect('Permission:');
        }
    }

    protected function createComponentAddEdit($name)
    {
        $form   = new Form($this, $name);
        $access = array(1 => 'Allow', 0 => 'Deny');
        // roles
        $mroles = new RolesModel($this->db);
        $roles  = $mroles->getTreeValues();
        // resources
        $resources[0] = '- All resources -';
        $mresources   = new ResourcesModel($this->db);
        $rows         = $mresources->getTreeValues();
        foreach ($rows as $key => $row) { // function array_merge does't work correctly with integer indexes
            // manual array merge
            $resources[$key] = $row;
        }
        // privileges
        $privileges[0] = '- All privileges -';
        $rows          = $this->db->fetchAll('SELECT id, name FROM [acl_privileges] ORDER BY name;');
        foreach ($rows as $row) { // function array_merge does't work correctly with integer indexes
            // manual array merge
            $privileges[$row->id] = $row->name;
        }

        //$renderer = $form->getRenderer();
        //$renderer->wrappers['label']['suffix'] = ':';
        //$form->addGroup('Add');
        $form->addMultiSelect('role_id', 'Role', $roles, 15)
            ->addRule(Form::FILLED, 'You have to fill roles.');
        $form->addMultiSelect('resource_id', 'Resources', $resources, 15)
            ->addRule(Form::FILLED, 'You have to fill resources.');
        $form->addMultiSelect('privilege_id', 'Privileges', $privileges, 15)
            ->addRule(Form::FILLED, 'You have to fill privileges.');
        //$form->addSelect('access', 'Access', $access)
        $form->addRadioList('access', 'Access', $access)
            ->addRule(Form::FILLED, 'You have to fill access.');
        $form->addSubmit('assign', 'Assign');
        $form->onSuccess[] = array($this, 'addEditOnFormSubmitted');
    }

    public function addEditOnFormSubmitted(Form $form)
    { // Permission form submitted
        $id     = $this->getParam('id');
        $values = $form->getValues();
        // add
        if (!$id) {
            $error = false;
            $this->db->begin();
            try {
                foreach ($values['privilege_id'] as $privi) {
                    foreach ($values['resource_id'] as $resou) {
                        foreach ($values['role_id'] as $role) {
                            if ($resou == '0')
                                $resou = null;
                            if ($privi == '0')
                                $privi = null;
                            $this->db->query('INSERT INTO [acl] (role_id, privilege_id, resource_id, access) VALUES (%i, %i, %i, %s);', $role, $privi, $resou, (bool)$values['access']);
                        }
                    }
                }
                $this->db->commit();
                $this->flashMessage('Permission was successfully assigned.', 'ok');
                unset($this->cache['acl']); // invalidate cache
                $this->redirect('Permission:');
            } catch (\Exception $e) {
                $error = false;
                $form->addError('Permission was not successfully assigned.');
                throw $e;
            }
            if ($error)
                $this->db->rollback();
        } else { // edit
            try {
                $values['access'] = (bool)$values['access'];
                $this->db->query('UPDATE [acl] SET %a WHERE id=%i;', $values, $id);
                $this->flashMessage('Permission was successfully edited.', 'ok');
                unset($this->cache['acl']); // invalidate cache
                $this->redirect('Permission:');
            } catch (Exception $e) {
                $form->addError('Permission was not successfully edited.');
                throw $e;
            }
        }
    }

    /******************
     * Delete
     ******************/
    public function actionDelete($id)
    {
        $sql = $this->db->query('SELECT a.id, a.access, ro.name AS role, re.name AS resource, p.name AS privilege
                                FROM [acl] AS a
                                LEFT JOIN [acl_roles] AS ro ON a.role_id=ro.id
                                LEFT JOIN [acl_resources] AS re ON a.resource_id=re.id
                                LEFT JOIN [acl_privileges] AS p ON a.privilege_id=p.id
                                WHERE a.id=%i;', $id);
        if (count($sql)) {
            $sql->setType('access', 'b');
            $acl                 = $sql->fetch();
            $this->template->acl = $acl;
        } else {
            $this->flashMessage('This acces does not exist.');
            $this->redirect('Permission:');
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
                $this->db->query('DELETE FROM [acl] WHERE id=%i;', $id);
                $this->flashMessage('The access has been deleted.', 'ok');
                unset($this->cache['acl']); // invalidate cache
                $this->redirect('Permission:');
            } catch (Exception $e) {
                $form->addError('The access has not been deleted.');
                throw $e;
            }
        } else
            $this->redirect('Permission:');
    }
}
