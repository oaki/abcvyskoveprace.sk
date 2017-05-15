<?php
namespace App\Model\Entity\File;

use App\Model\BaseDbModel;

class FileNodeModel extends BaseDbModel
{

    const NEW_DEFAULT_ID = 999999;

    protected $table = 'file_node';

    protected $id_name = 'id_file_node';

    function changeDefaultIdToNew($id_module, $id_file_node)
    {
        $this->connection->update($this->table, array('id_module' => $id_module))->where('id_module = %i', self::NEW_DEFAULT_ID, 'AND id_file_node = %i', $id_file_node)->execute();
    }

    function getFiles($id_file_node)
    {

//        return $this->loadWithSave('getFiles(' . $id_file_node . ')', function () use ($id_file_node) {
        $list = $this->getFluent()
            ->join('file')->using('(id_file_node)')
            ->where('id_file_node = %i', $id_file_node)
            ->orderBy('sequence')
            ->fetchAll();

        foreach ($list as $k => $l) {
            $list[$k]['default_file_param'] = unserialize($list[$k]['default_file_param']);
            $list[$k]['params']             = unserialize($list[$k]['params']);
            $list[$k]['image']              = $l['src'] . '.' . $l['ext'];
        }

        return $list;
//        });

    }

    function getFile($id_file_node)
    {
        return $this->loadWithSave('getFile(' . $id_file_node . ')', function () use ($id_file_node) {
            $list = $this->getFluent()
                ->join('file')->using('(id_file_node)')
                ->where('id_file_node = %i', $id_file_node)
                ->orderBy('sequence')
                ->fetch();

            if ($list) {
                $list['default_file_param'] = unserialize($list['default_file_param']);
                $list['params']             = unserialize($list['params']);
                $list['image']              = $list['src'] . '.' . $list['ext'];
            }

            return $list;
        });
    }

    /*
     * @param id_module
     * @param module_name
     * @return $id_file_node
     */
    function getIdFileNode($id_module, $module_name)
    {
        $id_file_node = $this->getFluent('id_file_node')
            ->where('file_node.id_module = %i', $id_module, 'AND module_name = %s', $module_name)
            ->fetchSingle();

        //ak neexistuje $id_file_node vytvori ho

        if (!$id_file_node)
            $id_file_node = $this->insertAndReturnLastId(array('id_module' => $id_module, 'module_name' => $module_name));

        return $id_file_node;
    }

    function getCountFiles($id_file_node)
    {
        return $this->getFluent('COUNT(id_file)')->join('file')->using('(id_file_node)')->where('id_file_node = %i', $id_file_node)->fetchSingle();
    }
}