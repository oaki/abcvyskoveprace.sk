<?php

class AdminBarControl extends BaseControl
{

    function render()
    {
        $user = $this->getService('user');

        if ($user->isLoggedIn() AND $user->isAllowed('cms', 'edit')) {
            $this->template->setFile(__DIR__ . "/default.latte");

            $presenter = $this->getPresenter();

            $presenter_name = $presenter->getName();

            switch ($presenter_name) {
                case 'Front:Product':
                    $id                   = $presenter->getParam('id');
                    $this->template->link = $presenter->link(":Admin:Product:edit", array('id' => $id));
                    $this->template->render();
                    break;
                case 'Front:Article':
                    $id   = $presenter->getParam('id');
                    $node = $this->getService('Node')->get($id);
//					dde($node);
                    $this->template->link = $presenter->link(':Admin:Cms:' . $node['service_name'] . ':default', array('id' => $node['id_node'], 'id_menu_item' => $node['id_menu_item']));
                    $this->template->render();
                    break;
            }

        }
    }
}