<?php
/**
 * Created by PhpStorm.
 * User: pavolbincik
 * Date: 10/23/16
 * Time: 9:06 PM
 */

namespace App\Components\Backend\DataGrid;

use Nette;

class DataGrid extends \Ublaboo\DataGrid\DataGrid
{

    public function __construct($parent, $name)
    {
        parent::__construct($parent, $name);

        DataGrid::$icon_prefix = 'icon icon-';
    }

    public function handleEdit($id, $key)
    {
        $column = $this->getColumn($key);
        $value  = $this->getPresenter()->getRequest()->getParameter('value');

        call_user_func_array($column->getEditableCallback(), [$id, $value]);
    }

}