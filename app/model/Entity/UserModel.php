<?php
namespace App\Model\Entity;

use App\Model\BaseDbModel;
use Nette\Caching\Storages\FileStorage;

class UserModel extends BaseDbModel
{

    const DB_KEY = 'a90fjsdif8k@uijlsdgfisdugfhlndzspg9spoildfg';

    function __construct(\DibiConnection $connection, FileStorage $cache)
    {
        parent::__construct($connection, $cache);
        $this->id_name = 'id_user';
        $this->table   = 'user';
    }

    public static function getHash($string)
    {
        return sha1(self::DB_KEY . $string);
    }

    function insert($values)
    {
        if (isset($values['password'])) {
            $values['password'] = self::getHash($values['password']);
        }

        parent::insert($values);
    }

    function update($values, $id_user)
    {
        if (isset($values['password'])) {
            $values['password'] = self::getHash($values['password']);
        }

        parent::update($values, $id_user);
    }

    function insertOrUpdateRoles($id_user, array $roles)
    {
        $this->deleteRolesForUser($id_user);
        foreach ($roles as $role_id) {
            $this->connection->insert('user_roles', array('user_id' => $id_user, 'role_id' => $role_id))->execute();
        }
    }

    function deleteRolesForUser($id_user)
    {
        $this->connection->delete('user_roles')
            ->where('user_id=%i', $id_user)->execute();
    }

    function getRoles($id_user)
    {

        return $this->connection->select('user_roles.role_id,key_name')
            ->from('acl_roles')
            ->rightJoin('user_roles')->on('acl_roles.id = user_roles.role_id')
            ->where('user_roles.user_id = %i', $id_user)
            ->fetchPairs('role_id', 'key_name');
    }

    function getRolesId($id_user)
    {

        return $this->connection->select('user_roles.role_id')
            ->from('acl_roles')
            ->rightJoin('user_roles')->on('acl_roles.id = user_roles.role_id')
            ->where('user_roles.user_id = %i', $id_user)
            ->fetchPairs('role_id', 'role_id');
    }

    function getAllCountry()
    {
        return $this->connection->select("*")->from('user_country')->where('active = 1')->orderBy('sequence')->fetchPairs('iso', 'country_name');
    }
}
