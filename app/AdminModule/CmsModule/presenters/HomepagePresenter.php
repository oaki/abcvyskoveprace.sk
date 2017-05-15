<?php
namespace App\AdminModule\CmsModule\Presenters;

use Nette\Application\UI\Form;

/**
 * Description of OrderPresenter
 *
 * @author oaki
 */
class HomepagePresenter extends BasePresenter
{

    function renderDefault($id_menu_item, $position)
    {

        //overenie ci existuje $id_menu_item
        if (!$this->getService('Page')->getFluent('1')->where('id_menu_item = %i', $id_menu_item)->fetchSingle()) {
            $this->redirect('Homepage:hello', array('id_menu_item' => null));
        }

        /*
         * Skusi zmazat nepouzite moduly
         */
        $this->getService('Node')->deleteUnsaveNode();

        // vyberie moduly pre danu poziciu
        $q = $this->getService('Node')->getFluent('id_node')->where('status != "not_in_system" AND position = %s', $position, 'AND id_menu_item = %i', $this->id_menu_item)->orderBy('sequence');

        //zisti pocet modulov pre vsetky pozicie
        $this->template->nodeCount = $this->getService('Node')->getFluent('position,COUNT(id_node) AS c')->where('status != "not_in_system"', 'AND id_menu_item = %i', $this->id_menu_item)->groupBy('position')->fetchPairs('position', 'c');

        $node_list = $q->fetchAll();

        $this->template->node_list = $this->getService('Node')->getListModuleInfo($node_list);

        /*
         * create link to each module
         */
        foreach ($this->template->node_list as $k => $l) {

            $this->template->node_list[$k]['link'] = $this->link(':Admin:Cms:' . $l['node_info']['service_name'] . ':default', array('id' => $l['id_node']));
        }

        $this->redrawControl('nodelist');
        $this->redrawControl('nodePositionBtn');
//		dde($this->template->node_list);

    }

    function handleSaveVisibility($id_node, $status)
    {

        if ($status == 'live') {
            $status = 'invisible';
        } else {
            $status = 'live';
        }

        $this->getService('Node')->update(array('status' => $status), $id_node);

        $this->flashMessage('Zobrazenie bolo zmenené');

        if ($this->isAjax()) {
            $this->redrawControl();
        } else {
            $this->redirect('this');
        }
    }

    function handleTest()
    {
        $this->flashMessage('Záznam bol zmazaný');
        if ($this->isAjax()) {

            $this->redrawControl('flashmessage');
        }
    }

    function handleDelete($id)
    {
        $this->getService('Node')->delete($id);
        $this->flashMessage('Záznam bol zmazaný');

        if ($this->isAjax()) {
            $this->redrawControl('nodelist');
            $this->redrawControl('flashmessage');
        } else {
            $this->redirect(':Admin:Cms:Homepage:default');
        }
    }

    function handleSaveOrder()
    {

        $post = $this->getPresenter()->request->getPost();

        $node  = $this->getService('Node');
        $order = $post['order'];

        if (is_array($order)) {

            foreach ($order as $k => $o) {

                $arr = array(
//					'parent_id'=>($o['parent_id']=='null')?NULL:$o['parent_id'],
//					'left'=>$o['left'],
//					'right'=>$o['right'],
//					'depth'=>$o['depth'],
                    'sequence' => $k,
                );

                $node->update($arr, $o['item_id']);
            }
        }

        $this->flashMessage('Poradie bolo zmenené.');

        if ($this->isAjax()) {
            $this->redrawControl('flashmessage');
        } else {
            $this->redirect('this');
        }

    }

    function handleMoveNodeToAnotherPage(Form $form)
    {
        $values = $form->getValues();

        if ($values['id_menu_item'] != '' AND $values['id_node'] != '') {
            $this->getService('Node')->update(array('id_menu_item' => $values['id_menu_item']), $values['id_node']);

            $this->flashMessage('Premiestnenie do inej kategórie bolo úspešné');
            $this->redirect('this', array('id_menu_item' => $values['id_menu_item']));
        } else {
            $this->flashMessage('Premiestnenie sa nepodarilo!', 'error');
            $this->redirect('this');
        }
    }

    function createComponentMoveNodeToAnotherPage($name)
    {
        $f = new Form();
//		$f->getElementPrototype()->class = 'ajax';

        $tree = $this->getService('Page')->getTreeUseInSelect($this->lang, $this->id_menu);

        //odstranenie rootu, aby to tam niekto nepridal
        if (isset($tree[''])) {
            unset($tree['']);
        }

        $f->addSelect('id_menu_item', 'Vyberte stránku', $tree)
            ->setRequired(true);

        $f->addHidden('id_node')
            ->setAttribute('id', 'moveNodeToAnotherPage__id_node');

//		$f['parent_id']->setItems($tree);
        $f->addSubmit('btn', 'Premiestniť');

        $f->onSuccess[] = array($this, 'handleMoveNodeToAnotherPage');

        $f->setDefaults(array('id_menu_item' => $this->id_menu_item));

        return $f;
    }

}
