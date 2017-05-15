<?php

namespace App\FrontModule\Components;

use App\Components\BaseControl;

class ArticleControl extends BaseControl
{

    public function render($id_node, $id_menu_item, $full = false)
    {

        $template          = $this->template;
        $template->article = $this->getService('Article')->get($id_node);
        $template->link    = $this->getPresenter()->link(':Front:Article:default', array('id' => $id_node, 'id_menu_item' => $id_menu_item));
        $template->setFile(dirname(__FILE__) . '/ArticleAnnotation.latte');

        if (strlen($template->article['text']) > 230) {
            $template->article['text'] = strip_tags(substr($template->article['text'], 0, strpos($template->article['text'], ' ', 230))) . "...";
        }

        $template->render();
    }

    protected function createComponent($name)
    {

        switch ($name) {
            case 'comment':
                return new CommentControl;
                break;
        }
    }

}