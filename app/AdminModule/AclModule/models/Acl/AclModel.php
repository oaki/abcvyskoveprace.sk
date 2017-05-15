<?php
namespace App\AdminModule\AclModule\Models;

class AclModel extends \Nette\Object
{

    private $connection;

    function __construct(\DibiConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Put in to array parents of specific role
     *
     * @param integer ID of parent role
     * @param string  Key name of parent role
     */
    public function getParentRole($parent_id, $parent_key, &$roles)
    {
        $sql  = $this->connection->query('SELECT id, key_name
                                FROM [acl_roles]
                                WHERE %and;', array('parent_id' => $parent_id));
        $rows = $sql->fetchAll();
        if (count($sql)) {
            foreach ($rows as $row) {
                $roles[] = array('key_name' => $row->key_name, 'parent_key' => $parent_key);
                $this->getParentRole($row->id, $row->key_name, $roles);
            }
        }
    }

    /**
     * Return all roles hierarchically ordered
     *
     * @return  array
     */
    public function getRoles()
    {
        $roles = array();
        $this->getParentRole(null, null, $roles);

        return $roles;
    }

    /**
     * Put in to array parents of specific resource
     *
     * @param integer ID of parent resource
     * @param string  Key name of parent resource
     * @param array   Array of all resource
     */
    public function getParentResource($parent_id, $parent_key, &$resources)
    {
        $sql  = $this->connection->query('SELECT id, key_name
                                FROM [acl_resources]
                                WHERE %and;', array('parent_id' => $parent_id));
        $rows = $sql->fetchAll();
        if (count($sql)) {
            foreach ($rows as $row) {
                $resources[] = array('key_name' => $row->key_name, 'parent_key' => $parent_key);
                $this->getParentResource($row->id, $row->key_name, $resources);
            }
        }
    }

    /**
     * Return all resources hierarchically ordered
     *
     * @return  array
     */
    public function getResources()
    {
        $resources = array();
        $this->getParentResource(null, null, $resources);

        return $resources;
    }

    /**
     * Return all rules of permissions
     *
     * @return  object
     */
    public function getRules()
    {
        $sql = $this->connection->query('
            SELECT
                a.access as access,
                ro.key_name as role,
                re.key_name as resource,
                p.key_name as privilege
                FROM [acl] a
                JOIN [acl_roles] ro ON (a.role_id = ro.id)
                LEFT JOIN [acl_resources] re ON (a.resource_id = re.id)
                LEFT JOIN [acl_privileges] p ON (a.privilege_id = p.id)
                ORDER BY a.id ASC
        ');

        $sql->setType('access', 'b');

        return $sql->fetchAll();
    }
}

/**
 * Acl object
 *
 * @author  Tomas Marcanik
 * @package GUI for Acl
 */
class Acl extends \Nette\Security\Permission
{

    public function __construct(\DibiConnection $connection)
    {

        $model = new AclModel($connection);

        $roles = $model->getRoles();
        foreach ($roles as $role)
            $this->addRole($role['key_name'], $role['parent_key']);

        $model->getResources();
        foreach ($model->getResources() as $resource)
            $this->addResource($resource['key_name'], $resource['parent_key']);

        foreach ($model->getRules() as $rule)
            $this->{$rule->access ? 'allow' : 'deny'}($rule->role, $rule->resource, $rule->privilege);
    }
}
