<?php
namespace App\AdminModule\CmsModule\Presenters;

/**
 * Admin_Cms_NodePresenter presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */

class NodePresenter extends BasePresenter
{

    function actionAdd($module_id, $position = 'content')
    {
        $arr = array(
            'id_menu_item'   => $this->id_menu_item,
            'id_user'        => $this->user->id,
            'id_type_module' => $module_id,
            'position'       => $position,//zatial default
        );

        $module_name = $this->getService('ModuleContainer')->fetch($module_id)->service_name;

        $link = ':Admin:Cms:' . $module_name . ':';

        $node_id = $this->getService('Node')->insertAndReturnLastId($arr);
        $this->getService($module_name)->addModuleToCms($node_id);

        $this->redirect($link, array('id' => $node_id));
        $this->terminate();
    }

}