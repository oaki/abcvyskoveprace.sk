<?php
namespace App\AdminModule\CmsModule\Presenters;

use App\AdminModule\Presenters\AdminPresenter;
use App\Components\SortableMenuControl;
use Nette\Security\AuthenticationException;

abstract class BasePresenter extends AdminPresenter
{

    /** @persistent */
    public $id_menu_item;

    /** @persistent */
    public $id_menu = 1;

    /** @persistent */
    public $id;

    /** @persistent */
    public $position = 'content';

    function startup()
    {
        parent::startup();

        if (!$this->user->isAllowed('spravca_obsahu', 'edit'))
            throw new AuthenticationException('Nedostatočne práva.');
    }

    public function beforeRender()
    {
        $this->template->langs = $this->getService('connection')->query("SELECT * FROM [lang] ORDER BY sequence")->fetchAssoc('iso');
    }

    function createComponent($name)
    {
        switch ($name) {
            case 'sortablemenu':

                $m = new \App\Components\Backend\SortableMenuControl();
                $m->setIdMenu($this->id_menu);
                $m->setLang($this->lang);

                return $m;
                break;

            default:
                return parent::createComponent($name);
                break;
        }
    }
}
