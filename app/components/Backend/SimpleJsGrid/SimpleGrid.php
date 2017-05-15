<?php

namespace App\Components;

use Nette\Application\UI\Control;
use Nette\Utils\Html;

class SimpleGrid extends Control
{

    /*
     * \DibiFluent
     */
    private $datasource;

    protected $cols = array();

    protected $actions = array();

    public $name = 'simpleGrid';

    protected $defaultRowParams;

    protected $defaultActionParams;

    const TEXT = "text";

    function __construct(\DibiFluent $dataSource, $params = array())
    {
        parent::__construct();

        $this->setDatasource($dataSource);

        // default parameters for each row
        $this->defaultRowParams = array(
            'truncate'              => 40,                        // NString truncate to length
            'width'                 => 100,                            // width of column
            'renderedWithTwoParams' => false,
            'renderer'              => function ($value) {
                return $value;
            },
            'class'                 => array(),                        // array of classes added to column
            'headerElement'         => Html::el("th"),    // helper to render the header cell
            'type'                  => self::TEXT                    // default column type
        );

        $this->defaultActionParams = array(
            'class'        => 'btn',
            'title'        => null,
            'text'         => null,
            'i_class'      => 'icon-pencil',
            'link_builder' => null
        );

    }

    function setDatasource(\DibiFluent $datasource)
    {
        $this->datasource = $datasource;
    }

    /**
     * Adds a columnt to the grid
     *
     * @param NString displayed name
     * @param NString column name (in db)
     * @param array   parameters for the column
     */
    public function addColumn($colName, $name, $params = array())
    {
        if (!is_array($params)) {
            throw(new \Exception("Third argument must be an array."));
        }

        $this->cols[$colName] = (object)array(
            'name'    => $name,
            'colName' => $colName,
            'params'  => (object)($params + $this->defaultRowParams)
        );

        return $this->cols[$colName];
    }

    public function addAction($name, $params = array())
    {

        $this->actions[] = (object)array(
            'name'   => $name,
            'params' => (object)($params + $this->defaultActionParams)
        );
    }

    function render()
    {
        $template = $this->template;
        $template->setFile(dirname(__FILE__) . '/default.latte');

        $template->list = $this->datasource->fetchAll();
//		dde($this->actions);
        $template->cols    = $this->cols;
        $template->actions = $this->actions;

        $template->render();

    }

}