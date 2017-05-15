<?php

namespace App\AdminModule\EshopModule\Components\SortableEshopCategory;

use App\Model\Entity\Eshop\CategoryModel;
use App\Model\Entity\LangModel;
use Nette\Application\UI\Control;
use Nette\ComponentModel\IContainer;
use Tracy\Debugger;

class SortableEshopCategoryControl extends Control
{

    protected $model, $id_lang, $langModel;

    function __construct(IContainer $parent = null, $name = null, CategoryModel $model, LangModel $langModel)
    {
        parent::__construct($parent, $name);
        $this->model     = $model;
        $this->langModel = $langModel;
    }

    public function render()
    {

        $t = $this->template;

        $t->setFile(dirname(__FILE__) . '/default.latte');

        $this->id_lang = $this->langModel->getDefaultLang();

        $t->tree = $this->model->getTree($this->id_lang);

        $t->editLink     = ':Admin:Eshop:Category:edit';
        $t->loadNodeLink = ':Admin:Eshop:Homepage:default';

        $t->render();
    }

    private function isSetOrNull($arr, $key)
    {
        return (isset($arr[$key])) ? ($arr[$key] === 'none' OR $arr[$key] == '') ? null : $arr[$key] : null;
    }

    function handleSaveOrder()
    {

        $order = json_decode((string)file_get_contents('php://input'), true);

        if (is_array($order)) {
            Debugger::log(count($order));
            foreach ($order as $k => $o) {

                $arr = array(
                    'id_parent' => $this->isSetOrNull($o, 'parent_id'),
                    'left'      => $this->isSetOrNull($o, 'left'),
                    'right'     => $this->isSetOrNull($o, 'right'),
                    'depth'     => $this->isSetOrNull($o, 'depth'),
                    'sequence'  => $k
                );

                $this->model->update($arr, $o['item_id']);
            }
        }

        $this->presenter->flashMessage('Poradie bolo zmenené.');
        $this->presenter->redrawControl('flashmessage');

    }

    function handleDelete($id)
    {

        $this->model->delete($id);

        $this->presenter->flashMessage('Položka bola vymazaná.');

        $this->redrawControl();
        $this->presenter->redrawControl('flashmessage');

        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        }
    }

}
