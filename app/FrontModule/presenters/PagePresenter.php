<?php
namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

class PagePresenter extends BasePresenter
{

    /** @persistent */
    public $id;

    public $back_url;

    function renderDefault($id)
    {

        if (!$id)
            throw new Nette\Application\BadRequestException(_('Stranka neexistuje'));

        $this->template->id_menu_item = $id;

        $this->template->page = $this->getService('Page')->fetch($id);

        /* ak je to homepage */
        if ($this->template->page['is_home'] == 1)
            $this->redirect(':Front:Homepage:default');

        $node = $this->getService('Node');

        $query       = $node->getFrontQuery($this->template->page['id_menu_item']);
        $query_count = clone $query;

        $vp                      = new \VisualPaginator();
        $paginator               = $vp->getPaginator();
        $paginator->itemsPerPage = 6;
        $paginator->itemCount    = $query_count->select(false)->select('COUNT(id_node)')->fetchSingle();

        $this->template->node_list = $query->limit($paginator->itemsPerPage)->offset($paginator->offset)->fetchAll();

        /*
         * Zobrazeni vychnych prom
         */
        $query                         = $node->getFrontQuery($this->template->id_menu_item, 'top');
        $this->template->top_node_list = $query->fetchAll();

//		dde($this->template->node_list);
        if ($paginator->itemCount == 1) {
            $node = current($this->template->node_list);

            switch ($node->service_name) {
                case 'Article':
                    $this->redirect('301', ':Front:Article:default', array('id' => $node->id_node));
                    break;
            }

        }
    }
}