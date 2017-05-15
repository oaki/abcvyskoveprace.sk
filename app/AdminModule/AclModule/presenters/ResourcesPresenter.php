<?php

/**
 * GUI Acl bootstrap file.
 *
 * @copyright  Copyright (c) 2010 Tomas Marcanik
 * @package    GUI Acl
 */

namespace App\AdminModule\AclModule\Presenters;

use App\AdminModule\AclModule\Models\ResourcesModel;
use Nette\Application\UI\Form;

/**
 * Resources
 *
 */
class ResourcesPresenter extends BasePresenter
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
        $nodes                   = new ResourcesModel($this->context->getService('connection'));
        $this->template->nodes   = $nodes;
        $this->template->parents = $nodes->getChildNodes(null);
    }

    /******************
     * Add and Edit
     ******************/
    public function actionAdd()
    {
    }

    public function actionEdit($id)
    {
        $sql  = $this->db->query('SELECT key_name, parent_id, name, comment FROM [acl_resources] WHERE id=%i;', $id);
        $form = $this->getComponent('addEdit');
        if (count($sql)) {
            $row = $sql->fetch();
            if (empty($row->parent_id))
                $row->parent_id = 0;
            $form->setDefaults($row);
        } else
            $form->addError('This resource does not exist.');
    }

    protected function createComponentAddEdit($name)
    {
        $resources[0] = ' ';
        $mresources   = new ResourcesModel($this->db);
        $rows         = $mresources->getTreeValues();
        foreach ($rows as $key => $row) { // function array_merge does't work correctly with integer indexes
            // manual array merge
            $resources[$key] = $row;
        }

        $form                                  = new Form($this, $name);
        $renderer                              = $form->getRenderer();
        $renderer->wrappers['label']['suffix'] = ':';
        //$form->addGroup('Edit');

        $form->addText('name', 'Name', 30)
            ->addRule(Form::FILLED, 'You have to fill name.');

        $form->addText('key_name', 'Key', 30);
        $form->addSelect('parent_id', 'Parent', $resources, 15);
        $form->addTextArea('comment', 'Comment', 40, 4)
            ->addRule(Form::MAX_LENGTH, 'Comment must be at least %d characters.', 250)
            ->setRequired();
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
                if ($values['parent_id'] == 0)
                    $values['parent_id'] = null;
                $this->db->query('INSERT INTO [acl_resources] %v;', $values);
                $this->flashMessage('The resource has been added.', 'ok');
                unset($this->cache['acl']); // invalidate cache
                $this->redirect('Resources:');
            } catch (\Exception $e) {
                $form->addError('The resource has not been added.');
                throw $e;
            }
        } else { // edit
            try {
                $id     = $this->getParam('id');
                $values = $form->getValues();
                if ($values['parent_id'] == 0)
                    $values['parent_id'] = null;
                $this->db->query('UPDATE [acl_resources] SET %a WHERE id=%i;', $values, $id);
                $this->flashMessage('The resource has been edited.', 'ok');
                unset($this->cache['acl']); // invalidate cache
                $this->redirect('Resources:');
            } catch (\Exception $e) {
                $form->addError('The resource has not been edited.');
                throw $e;
            }
        }
    }

    /******************
     * Delete
     ******************/
    public function actionDelete($id)
    {
        $sql = $this->db->query('SELECT name FROM [acl_resources] WHERE id=%i;', $id);
        if (count($sql)) {
            $this->template->resource = $sql->fetchSingle();
        } else {
            $this->flashMessage('This resource does not exist.');
            $this->redirect('Resources:');
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
                $this->db->query('DELETE FROM [acl_resources] WHERE id=%i;', $id);
                $this->flashMessage('The resource has been deleted.', 'ok');
                unset($this->cache['acl']); // invalidate cache
                $this->redirect('Resources:');
            } catch (\Exception $e) {
                $form->addError('The resource has not been deleted.');
                throw $e;
            }
        } else
            $this->redirect('Resources:');
    }
}
