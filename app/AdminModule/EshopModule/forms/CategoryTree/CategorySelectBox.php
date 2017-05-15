<?php

namespace EshopModule\Form;

use Nette\Forms\Controls\MultiSelectBox;
use Nette\Utils\Html;

class CategorySelectBox extends MultiSelectBox
{

    private $tree, $template;

    function __construct($label = null, array $items = null, $tree, $template)
    {
        parent::__construct($label, $items);

        $this->tree     = $tree;
        $this->template = $template;
    }

    function getControl()
    {
        $control = parent::getControl();
        $control->addAttributes(array('class' => 'sempridehide'));

        $container = Html::el('div');

        $container->addHtml($control);

        $ul_div = Html::el('div');
        $ul_div->setHtml($this->renderTree($this->tree));

        $container->addHtml($ul_div);

        return $container;
    }

    function renderTree()
    {
        $t = $this->template;
        $t->setFile(dirname(__FILE__) . '/categoryTree.latte');
        $t->tree = $this->tree;

        return $t;
    }

}