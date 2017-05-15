<?php
namespace App\Model\Entity;

use App\Model\BaseDbModel;
use Nette\Caching\Storages\FileStorage;
use Nette\DI\Container;

class NodeModel extends BaseDbModel // object je Nette\Object
{

    private $context;

    function __construct(\DibiConnection $connection, FileStorage $cache, Container $context)
    {
        parent::__construct($connection, $cache);
        $this->table   = 'node';
        $this->id_name = 'id_node';
        $this->context = $context;
    }

    /*
     * vrati list (*) aktivych (live), zoradenych, kde id_menu_item ==/..
     * pouziva sa pri fronte
     */
    public function getFrontQuery($id_menu_item, $position = 'content')
    {
        return $this->getFluent()
            ->join('type_module')->using('(id_type_module)')
            ->where('
					 status = "live" 
					 AND id_menu_item = %i', $id_menu_item, "
					AND position = %s", $position)
            ->orderBy('sequence');
    }

    function putInToTheSystem($id_node)
    {
        //over ci je status not_in_system, ak je prepis na live
        $l = $this->getFluent('id_node')->where("id_node = %i", $id_node, "AND status = 'not_in_system'")->fetchSingle();
        if ($l) {
            $this->update(array('status' => 'live'), $id_node);
        }
    }

    /*
     * Vymazanie zo systemu moduly ktore neboli ulozene
     */
    function deleteUnsaveNode()
    {
        //vymaze az po 2 hodinach ak sa nezmeni stav
        $list = $this->getFluent()->where("status = 'not_in_system' AND add_date < ( NOW() - 60*2 )")->fetchAll();

        if (!empty($list)) {

            foreach ($list as $l) {

                $this->delete($l['id_node']);

                $module_name = $this->context->getService('ModuleContainer')->fetch($l['id_type_module'])->service_name;

                $this->context->getService($module_name)->delete($l['id_node']);
            }
        }
    }

    /*
     * get all info about module
     * @input $id_node
     * @return array $module_info
     */
    public function getListModuleInfo($node_list)
    {
        $return = array();

        foreach ($node_list as $n) {
            $return[] = $this->getModuleInfo($n->id_node);
        }

        return $return;
    }

    /*
     * get info about module
     * @input $id_node
     * @return array $module_info
     */
    public function getModuleInfo($id_node)
    {
        $node_info = $this->get($id_node);

        if (!$node_info)
            throw new Exception('Not exist id_node');

        $module_info = $this->context->getService($node_info->service_name)->fetch($id_node);

        if (!$module_info)
            throw new Exception('Not exist id_node in service');

        $module_info['node_info'] = $node_info;

//		switch($node_info->service_name){
//			case 'Article':
//				$module_info['node_info']['node_name'] = $module_info['title'];
//				$module_info['node_info']['node_desc'] = $module_info['text'];
//				break;
//		}
//		dde($module_info);
        $module_info['node_title'] = $this->context->getService($node_info->service_name)->identifyTitleAndDescriptionForNode($module_info);

        return $module_info;
    }

    public function get($id_node)
    {
        $c = $this->connection->select('*')
            ->from('node')
            ->join('type_module')->using('(id_type_module)')
            ->where('id_node = %i', $id_node);

        return $c->fetch();
    }

    function delete($id)
    {
        $node_info = $this->get($id);
        $this->context->getService($node_info->service_name)->delete($id);
        parent::delete($id);
    }

    /*
     * @vstup array() $node_list
     * zisti vsetky informacie o module
     * @return $node_list with all necessary info
     */

    public function slugToId($slug)
    {

        $slug = rtrim($slug, '/');
        echo $slug;
        exit;
        $key = 'slugToId' . $slug;

        if (!isset($this->cache[$key])) {
            $id = $this->connection->select('id_node')->from('node')->where('url_identifier LIKE %s', $slug)->fetchSingle();
            if (!$id) $id = null;
            $this->cache[$key] = $id;
        }

        return $this->cache[$key];
    }

    public function idToSlug($id)
    {

        $key = 'idToSlug' . $id;

        if (!isset($this->cache[$key])) {
            $name = $this->connection->select('url_identifier')->from('node')->where('id_menu_item = %i', $id)->fetchSingle();

            if (!$name) $name = null;
            $this->cache[$key] = $name;
        }

        return $this->cache[$key];
    }

}