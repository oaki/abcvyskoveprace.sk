<?php
/**
 * @author Pavol (Lopo) Hluchý
 */
namespace App\Components\Backend\TreeView;

use Latte\Runtime\Html;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;

class CBTree
    extends Control
{

    /** @var Html  container element template */
    protected $container;

    /** @var TreeView data */
    protected $tree;

    /** @var string CB column */
    public $checkColumn = 'cb';

    /** @var bool */
    public $checkParents = true;

    /** @var bool */
    public $checkChildren = false;

    /** @var bool */
    public $uncheckChildren = true;

    /** @var string 'expand' (fully expanded), 'collapse' (fully collapsed) or 'default' */
    public $initialState = 'default';

    /** @var string name column */
    public $nameColumn = 'name';

    /**
     * @param  string     label
     * @param  TreeView   options from which to choose
     */
    public function __construct($label, TreeView $tree)
    {
        parent::__construct($label);
        $this->control->type = 'checkbox';
        $this->container     = Html::el();
        if ($tree !== null)
            $this->setItems($tree);
    }

    /**
     * Returns selected checkbox value.
     *
     * @param  bool raw
     *
     * @return mixed
     */
    public function getValue($raw = false)
    {
        return is_array($this->value) ? $this->value : null;
    }

    /**
     * Form container extension method. Do not call directly.
     *
     * @param  Form
     * @param  string  name
     * @param  string  label
     * @param  array   items
     *
     * @return CBTree
     */
    public static function addCBTree(Form $form, $name, $label, $tree)
    {
        return $form[$name] = new self($label, $tree);
    }

    /**
     * Sets options from which to choose.
     *
     * @param TreeView tree
     *
     * @return CBTree provides a fluent interface
     */
    public function setItems(TreeView $tree)
    {
        $this->tree = $tree;

        return $this;
    }

    /**
     * Returns container HTML element template.
     * @return Html
     */
    final public function getContainerPrototype()
    {
        return $this->container;
    }

    /**
     * Generates control's HTML element.
     *
     * @param  mixed key
     *
     * @return Html
     */
    public function getControl($key = null)
    {
        if ($key === null)
            $container = clone $this->container;
        elseif (!isset($this->tree[$key]))
            return null;
        $control = parent::getControl();
        $name    = $control->name;
        $control->name .= '[]';
        $id    = $control->id;
        $label = Html::el('label');
        $ul    = Html::el('ul', array('id' => $name));
        $container->add(Html::el('script', array('type' => "text/javascript"))
            ->add("$(function(){\$('ul#$name').collapsibleCheckboxTree({ checkParents: " . ($this->checkParents ? 'true' : 'false') . ", checkChildren: " . ($this->checkChildren ? 'true' : 'false') . ", uncheckChildren: " . ($this->uncheckChildren ? 'true' : 'false') . ", initialState: '" . $this->initialState . "'});});")
        );
        $container->add(Html::el('style')
            ->add("#$name ul { padding-left:20px;}#$name li { list-style:none;}#$name ul.hide { display:none;}#$name span { color:#999;font-family:'Courier New', Courier, monospace;cursor:default;}#$name span.expanded, #$name span.collapsed { cursor:pointer;}#$name label { float: none; display: inline;}")
        );
        foreach ($this->tree->getNodes() as $node)
            $ul->add($this->renderNode($node, $control, $label));
        $container->add($ul);

        return $container;
    }

    /**
     * Generates label's HTML element.
     *
     * @param caption
     *
     * @return Nette\Web\Html
     */
    public function getLabel($caption = null)
    {
        $label      = parent::getLabel($caption);
        $label->for = null;

        return $label;
    }

    /**
     * Render 1 node (+ subnodes)
     *
     * @param TreeViewNode node
     * @param control
     * @param label
     *
     * @return Nette\Web\Html
     */
    private function renderNode(TreeViewNode $node, $control, $label)
    {
        $pcontrol    = clone $control;
        $li          = Html::el('li');
        $nid         = $node->getDataRow()->id;
        $control->id = $label->for = $control->id . '-' . $nid;
        $ck          = $this->checkColumn;

        /*
         * Pridal som ze ak neezistuje stlpec, aby nedavalo notice
         */
        if (isset($node->getDataRow()->$ck) AND $node->getDataRow()->$ck)
            $control->checked = 'checked';
        else
            $control->checked = null;
        $control->value = $nid;
        $nc             = $this->nameColumn;
        $label->setText($node->getDataRow()->$nc);
        $li->add((string)$control . (string)$label);
        $nodes = $node->getNodes();
        if (count($nodes)) {
            $ul = Html::el('ul');
            $li->add($ul);
            foreach ($nodes as $n)
                $ul->add($this->renderNode($n, $pcontrol, $label));
        }

        return $li;
    }
}