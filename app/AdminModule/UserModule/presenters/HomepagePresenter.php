<?php

/**
 * Description of OrderPresenter
 *
 * @author oaki
 */
namespace App\AdminModule\UserModule\Presenters;

use App\AdminModule\AclModule\Models\RolesModel;
use App\AdminModule\UserModule\Forms\UserBaseForm;
use App\Components\Backend\File\FileControl;
use App\Components\SimpleGrid;
use App\Model\Entity\File\FileNodeModel;
use App\Model\Entity\UserModel;
use Nette\Application\UI\Form;

class HomepagePresenter extends BasePresenter
{

    private $userModel;

    function renderDefault()
    {

//        $this['header']['js']->addFile('/jquery/DataTables-1.9.3/media/js/jquery.dataTables.min.js');
//
//        $this['header']['css']->addFile('../components/simpleGrid/DT_bootstrap.css');
//
//        $this['header']['js']->addFile('../components/simpleGrid/DT_bootstrap.js');
    }

    function renderEdit($id)
    {
        if ($this->user->isInRole('makler') AND $id != $this->user->getId())
            $this->redirect('edit', array('id' => $this->user->getId()));
    }

    function renderAdd()
    {

    }

    function handleDeleteUser($id)
    {

        $this->userModel->delete($id);
        $this->flashMessage('Uživateľ bol úspešne zmazaný.');

        if ($this->isAjax()) {
            $this->invalidateControl('flashmessage');
            $this['simpleGrid']->invalidateControl();
        } else {
            $this->redirect('this');
        }
    }

    function handleAddUser(Form $form)
    {
        $values = $form->getValues();

        $values['activate'] = 1;
        unset($values['passwordCheck']);

        if (isset($values['user_roles'])) {
            $_tmp = $values['user_roles'];
            unset($values['user_roles']);
        }

        $new_user_id = $this->userModel->insertAndReturnLastId($values);

        $this->userModel->insertOrUpdateRoles($new_user_id, $_tmp);

        $this->getService('FileNode')->changeDefaultIdToNew($new_user_id, $this->getService('FileNode')->getIdFileNode(FileNodeModel::NEW_DEFAULT_ID, 'User'));
        $this->flashMessage('Uživateľ bol úspešne pridaný.');

        $this->redirect('default');
    }

    function handleActivate($id)
    {
        $u = $this->userModel->getFluent()->where('id_user = %i', $id)->fetch();
        if ($u['activate'] == 1) {
            $activate = 0;
        } else {
            $activate = 1;
        }

        if ($activate) {
            $this->flashMessage('Účet bol aktivovaný.');
        } else {
            $this->flashMessage('Účet bol deaktivovaný');
        }

        $this->userModel->update(array('activate' => $activate), $id);

        $this->invalidateControl('flashmessage');

        $this['simpleGrid']->invalidateControl();

    }

    function handleEditUser(Form $form)
    {
        $values = $form->getValues();

        unset($values['passwordCheck']);

        //ak nevyplni heslo, zostava stare
        if ($values['password'] == '')
            unset($values['password']);

        $_tmp = array();
        if (isset($values['user_roles'])) {
            $_tmp = $values['user_roles'];
            unset($values['user_roles']);
        }
//		dde($_tmp);
        $this->userModel->update($values, $this->id);
        $this->userModel->insertOrUpdateRoles($this->id, $_tmp);

        $this->flashMessage('Uživateľ bol upravený.');

        if ($this->isAjax()) {
            $this->invalidateControl('flashmessage');
        } else {
            $this->redirect('this');
        }
    }

    function createComponent($name)
    {
        switch ($name) {
            case 'simpleGrid':
                $grid = new SimpleGrid($this->userModel->getFluent());
//				$grid->setDatasource(ProductModel::getFluent());

                $grid->addColumn('id_user', 'ID');
                $grid->addColumn('name', 'Meno');
                $grid->addColumn('surname', 'Priezvisko');
                $grid->addColumn('login', 'Login');

                $grid->addColumn('activate', 'Aktívny', array('renderer' => function ($value) {
                    if ($value == 1)
                        return 'Aktívny';
                    else
                        return 'Neaktívny';
                }));

                $presenter = $this;
                $grid->addAction('activate', array(
                        'class'        => 'btn ajax',
                        'i_class'      => 'icon-retweet',
                        'title'        => 'Aktivovať / Deaktivovať',
                        'link_builder' => function ($row) use ($presenter) {
                            return $presenter->link('activate!', array('id' => $row->id_user));
                        })
                );
                $grid->addAction('edit', array(
                        'class'        => 'btn',
                        'i_class'      => 'icon-pencil',
                        'link_builder' => function ($row) use ($presenter) {
                            return $presenter->link('edit', array('id' => $row->id_user));
                        })
                );

                $grid->addAction('delete', array(
                        'class'        => 'btn btn-danger confirm-ajax',
                        'i_class'      => 'icon-trash icon-white',
                        'link_builder' => function ($row) use ($presenter) {
                            return $presenter->link('deleteUser!', array('id' => $row->id_user));
                        })
                );

                return $grid;
                break;

            case 'addUserForm':
                $f = new UserBaseForm($this->getService('User'), $this, 'addUserForm');

                if ($this->user->isInRole('admin')) {
                    $roleModel = new RolesModel($this->getService('connection'));
                    $role_tree = $roleModel->getTreeValues();
                    $f->addGroup('Rola');
                    $f->addMultiSelect('user_roles', 'Rola (skupina)', $role_tree);
                }

                $f->getElementPrototype()->class = 'ajax';

                $f['password']->setAttribute('autocomplete', 'off');

                $f->addSubmit('btn', 'Pridať')
                    ->setAttribute('class', 'btn btn-success');

                $f->onSuccess[] = array($this, 'handleAddUser');

                return $f;
                break;

            case 'editUserForm':
                $f = new UserBaseForm($this->getService('User'), $this, 'editUserForm');

                if ($this->user->isInRole('admin')) {
                    $roleModel = new RolesModel($this->getService('connection'));
                    $role_tree = $roleModel->getTreeValues();
                    $f->addGroup('Rola');
                    $f->addMultiSelect('user_roles', 'Rola (skupina)', $role_tree);
                }

                $f->getElementPrototype()->class = 'ajax';

                $f['password']->setAttribute('autocomplete', 'off');

                if ($this->user->isInRole('admin')) {
                    $f->addGroup('Aktivácia');
                    $f->addSelect('activate', 'Je aktivovaný?', array('0' => 'nie', '1' => 'áno'));
                }
                $f->addSubmit('btn', 'Upraviť')
                    ->setAttribute('class', 'btn btn-success');

                $info = $this->userModel->fetch($this->id);
//				$info['user_roles']['role_ids'] = $this->userModel->getRoles($this->id);
                unset($info['password']);

                $f->setDefaults($info);

                $role_ids = $this->userModel->getRolesId($this->id);
//				dde($role_ids);
                $f->setDefaults(array('user_roles' => $role_ids));

                $f->onSuccess[] = array($this, 'handleEditUser');

                return $f;
                break;

            default:
                return parent::createComponent($name);
                break;
        }
    }

    function createComponentFile($name)
    {

        $f = new FileControl($this, $name);

        if ($this->id == null) {
            $id = FileNodeModel::NEW_DEFAULT_ID;
        } else {
            $id = $this->id;
        }
        $f->setIdFileNode($this->getService('FileNode')->getIdFileNode($id, 'User'));

        $f->addInput(array('type' => 'text', 'name' => 'name', 'css_class' => 'input-medium', 'placeholder' => 'Sem umiestnite popis'));

        $f->saveDefaultInputTemplate();

        return $f;
    }

    public function injectUserModel(UserModel $model)
    {
        $this->userModel = $model;
    }
}
