<?php

namespace App\Components\Backend;

use App\Components\BaseControl;

/**
 * @property-read PageModel $pageModel
 */
class SortableMenuControl extends BaseControl
{

    public $id_menu, $lang;

    public function setIdMenu($id_menu)
    {
        $this->id_menu = $id_menu;
    }

    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    public function render()
    {

        $t = $this->template;

        $t->setFile(dirname(__FILE__) . '/default.latte');

        $t->tree = $this->getService('Page')->fetchAssoc($this->id_menu, $this->lang);

        $t->editLink     = ':Admin:Cms:Page:editItem';
        $t->loadNodeLink = ':Admin:Cms:Homepage:default';

        $t->render();
    }

    function handleSaveOrder()
    {

        $order = $this->getPresenter()->request->getPost();
        $order = $order['sortablemenu-order'];

        $page = $this->getService('Page');

        if (is_array($order)) {

            foreach ($order as $k => $o) {

                $arr = array(
                    'parent_id' => ($o['parent_id'] == 'null') ? null : $o['parent_id'],
                    'left'      => $o['left'],
                    'right'     => $o['right'],
                    'depth'     => $o['depth'],
                    'sequence'  => $k,
                );

                $page->update($arr, $o['item_id']);

            }
        }

        $this->presenter->flashMessage('Poradie bolo zmenené.');

        $this->presenter->redrawControl('flashmessage');

    }

    function handleDelete($id)
    {

        $page = $this->getService('Page');

        $page->delete($id);

        $this->presenter->flashMessage('Položka bola vymazaná.');

        $this->redrawControl();
        $this->presenter->redrawControl('flashmessage');
        $this->presenter->redrawControl('nodelist');

        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        }

    }

}