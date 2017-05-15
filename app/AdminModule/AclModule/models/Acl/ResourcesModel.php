<?php

namespace App\AdminModule\AclModule\Models;

class ResourcesModel extends \Nette\Object
{

    private $connection;

    function __construct(\DibiConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Has parent of node children?
     *
     * @param   integer Parent id
     *
     * @return  integer Number of children
     */
    public function hasChildNodes($parent_id)
    {
        $sql = $this->connection->query('SELECT * FROM [acl_resources] WHERE %and;', array('parent_id' => $parent_id));

        return count($sql);
    }

    /**
     * Return all children of specific parent of node
     *
     * @param   integer Parent id
     *
     * @return  object
     */
    public function getChildNodes($parent_id)
    {
        $sql = $this->connection->query('SELECT id, name, comment FROM [acl_resources] WHERE %and ORDER BY name;', array('parent_id' => $parent_id));

        return $sql->fetchAll();
    }

    /**
     * Return all resources in the tree structure
     *
     * @return  array
     */
    public function getTreeValues()
    {
        $resources = array();
        $this->getParents(null, $resources, 0);

        return $resources;
    }

    /**
     * All children of specific parent of resources placed in a array
     *
     * @param   integer Parent id
     * @param   array   Array of curent resources
     * @param   integer Depth of tree structure
     */
    public function getParents($parent_id, &$array, $depth)
    {
        $sql  = $this->connection->query('SELECT id, name FROM [acl_resources] WHERE %and ORDER BY name;', array('parent_id' => $parent_id));
        $rows = $sql->fetchAll();
        foreach ($rows as $row) {
            $array[$row->id] = ($depth ? str_repeat('- - ', $depth) : '') . $row->name;
            $this->getParents($row->id, $array, ($depth + 1));
        }
    }
}

?>
