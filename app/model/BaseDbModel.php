<?php
namespace App\Model;

use Nette\Caching\Storages\FileStorage;
use Nette\Caching\Cache;

class BaseDbModel extends CacheModel implements IEntity
{

    /**
     * @property-read \DibiConnection $connection
     */
    protected $connection;

    protected $table = '';

    protected $convention = 'id__TABLENAME__';

    protected $id_name = false;

    public function __construct(\DibiConnection $connection, FileStorage $fileStorage)
    {
        $this->connection = $connection;

        $this->cache = new Cache($fileStorage, $this->table);
    }

    public function getTableRows()
    {

        return $this->connection->query("SHOW COLUMNS FROM " . $this->table)->fetchPairs('Field', 'Field');

        $key = $this->getTableIdName() . '_getTableRows()';
        $r   = $this->loadCache($key);
        if ($r)
            return $r;

        return $this->saveCache($key, $this->connection->query("SHOW COLUMNS FROM " . $this->table)->fetchPairs('Field', 'Field'));
    }

    public function getConnection()
    {
        return $this->connection;
    }

    function insertAndReturnLastId($values)
    {
        $this->insert($values);

        return $this->connection->insertId();
    }

    function insert($values = [])
    {
        $this->connection->insert($this->table, $values)->execute();
        $this->invalidateCache();
    }

    function delete($id)
    {
        $this->connection->delete($this->table)->where($this->getTableIdName() . '=%i', $id)->execute();
        $this->invalidateCache($id);
    }

    function update($values, $id)
    {
        $this->connection->update($this->table, $values)->where($this->getTableIdName() . '=%i', $id)->execute();
        $this->invalidateCache($id);
    }

    function getFluent($select_collums = '*')
    {
        return $this->connection->select($select_collums)->from($this->table);
    }

    function fetch($id)
    {
        $key = $this->getTableIdName() . '_fetch( ' . $id . ')';
        $r   = $this->loadCache($key);
        if ($r)
            return $r;

        return $this->saveCache($key, $this->getFluent()->where($this->getTableIdName() . '=%i', $id)->fetch(), $id);
    }

    public function getTableIdName()
    {
        return ($this->id_name) ? $this->id_name : str_replace('_TABLENAME__', $this->table, $this->convention);
    }

    public function getTableName()
    {
        return $this->table;
    }
}