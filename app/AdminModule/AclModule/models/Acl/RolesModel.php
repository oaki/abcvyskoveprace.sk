<?php
namespace App\AdminModule\AclModule\Models;

class RolesModel extends \Nette\Object
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
        $sql = $this->connection->query('SELECT * FROM [acl_roles] WHERE %and;', array('parent_id' => $parent_id));

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
        $sql = $this->connection->query('SELECT r.id, r.name, r.comment, count(ur.user_id) AS members
                                FROM [acl_roles] AS r
                                LEFT JOIN [user_roles] AS ur ON r.id=ur.role_id
                                WHERE %and
                                GROUP BY r.id, r.name, r.comment
                                ORDER BY r.name;', array('r.parent_id' => $parent_id));

        return $sql->fetchAll();
    }

    /**
     * Return all roles in the tree structure
     *
     * @return  array
     */
    public function getTreeValues()
    {
        $roles = array();
        $this->getParents(null, $roles, 0);

        return $roles;
    }

    /**
     * All children of specific parent of role placed in a array
     *
     * @param   integer Parent id
     * @param   array   Array of curent resources
     * @param   integer Depth of tree structure
     */
    public function getParents($parent_id, &$array, $depth)
    {
        $sql  = $this->connection->query('SELECT id, name FROM [acl_roles] WHERE %and ORDER BY name;', array('parent_id' => $parent_id));
        $rows = $sql->fetchAll();
        foreach ($rows as $row) {
            $array[$row->id] = ($depth ? str_repeat("- - ", $depth) : '') . $row->name;
            $this->getParents($row->id, $array, ($depth + 1));
        }
    }
}

?>
