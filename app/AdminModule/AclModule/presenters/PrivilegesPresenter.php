<?php

/**
 * GUI Acl bootstrap file.
 *
 * @copyright  Copyright (c) 2010 Tomas Marcanik
 * @package    GUI Acl
 */

namespace App\AdminModule\AclModule\Presenters;

use Nette\Application\UI\Form;

/**
 * Privileges
 *
 */
class PrivilegesPresenter extends BasePresenter
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
        $sql                        = $this->db->query('SELECT id, name, comment FROM [acl_privileges] ORDER BY name;');
        $this->template->privileges = $sql->fetchAll();
    }

    /******************
     * Add and Edit
     ******************/
    public function actionAdd()
    {
    }

    public function actionEdit($id)
    {
        $sql  = $this->db->query('SELECT key_name, name, comment FROM [acl_privileges] WHERE id=%i;', $id);
        $form = $this->getComponent('addEdit');
        if (count($sql)) {
            $form->setDefaults($sql->fetch());
        } else
            $form->addError('This privileg does not exist.');
    }

    protected function createComponentAddEdit($name)
    {
        $form                                  = new Form($this, $name);
        $renderer                              = $form->getRenderer();
        $renderer->wrappers['label']['suffix'] = ':';
        $form->addText('name', 'Name', 30)
            ->addRule(Form::FILLED, 'You have to fill name.');
        //$form->addGroup('Edit');
        $form->addText('key_name', 'Key', 30)
            ->setDisabled(true);
        $form->addTextArea('comment', 'Comment', 40, 4)
            ->addRule(Form::MAX_LENGTH, 'Comment must be at least %d characters.', 250)
            ->setRequired(true);
        if ($this->getAction() == 'add')
            $form->addSubmit('add', 'Add');
        else
            $form->addSubmit('edit', 'Edit');
        $form->onSuccess[] = array($this, 'addEditOnFormSubmitted');
    }

    public function addEditOnFormSubmitted(Form $form)
    {
        // add
        if ($this->getAction() == 'add') {
            try {
                $values = $form->getValues();
                $this->db->query('INSERT INTO [acl_privileges] %v;', $values);
                $this->flashMessage('The privileg has been added.', 'ok');
                unset($this->cache['acl']); // invalidate cache
                $this->redirect('Privileges:');
            } catch (\Exception $e) {
                $form->addError('The privileg has not been added.');
                throw $e;
            }
        } else { // edit
            try {
                $id     = $this->getParameter('id');
                $values = $form->getValues();
                $this->db->query('UPDATE [acl_privileges] SET %a WHERE id=%i;', $values, $id);
                $this->flashMessage('The privileg has been edited.', 'ok');
                unset($this->cache['acl']); // invalidate cache
                $this->redirect('Privileges:');
            } catch (\Exception $e) {
                $form->addError('The privileg has not been edited.');
                throw $e;
            }
        }
    }

    /******************
     * Delete
     ******************/
    public function actionDelete($id)
    {
        $sql = $this->db->query('SELECT name FROM [acl_privileges] WHERE id=%i;', $id);
        if (count($sql)) {
            $this->template->privilege = $sql->fetchSingle();
        } else {
            $this->flashMessage('This privilege does not exist.');
            $this->redirect('Privileges:');
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
                $this->db->query('DELETE FROM [acl_privileges] WHERE id=%i;', $id);
                $this->flashMessage('The privilege has been deleted.', 'ok');
                unset($this->cache['acl']); // invalidate cache
                $this->redirect('Privileges:');
            } catch (\Exception $e) {
                $form->addError('The privilege has not been deleted.');
                throw $e;
            }
        } else
            $this->redirect('Privileges:');
    }
}
