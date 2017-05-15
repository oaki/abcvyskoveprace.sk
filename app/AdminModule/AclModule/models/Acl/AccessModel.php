<?php
/**
 * Access model
 *
 * @author  Tomas Marcanik
 * @package GUI for Acl
 */
namespace App\AdminModule\AclModule\Models;

class AccessModel extends \Nette\Object
{

    /** @var array */
    private $access = array();

    private $connection;

    /**
     * @param array Array of roles
     */
    public function __construct($roles, \DibiConnection $connection)
    {
        $this->connection = $connection;
        $resources        = $this->connection->fetchAll('SELECT key_name, name FROM [acl_resources] ORDER BY name;');
        $privileges       = $this->connection->fetchAll('SELECT key_name, name FROM [acl_privileges] ORDER BY name;');

        $acl = new Acl($this->connection);
        $i   = 0;
        foreach ($resources as $res) {
            foreach ($privileges as $pri) {
                foreach ($roles as $role) {
                    if ($acl->isAllowed($role->key_name, $res->key_name, $pri->key_name)) {
                        $this->access[$i]['resource'] = $res->name;
                        $this->access[$i]['privileg'] = $pri->name;
                        $i++;
                        break 1;
                    }
                }
            }
        }
    }

    /**
     * @return  array Resources and privileges for current roles
     */
    public function getAccess()
    {
        return $this->access;
    }
}
