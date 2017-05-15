<?php
namespace App\Model;

use Nette\Caching\Cache;
use Nette\Object;

abstract class CacheModel extends Object
{

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @return mixed
     */
    public function invalidateCache($id = null)
    {
        return $this->cache->clean([
            Cache::TAGS => $this->getTags($id)
        ]);
    }

    public function getTags($id)
    {
        return [get_class($this), get_class($this) . '/' . $id];
    }

    /**
     * @return mixed
     */
    public function getCache()
    {
        return $this->cache;
    }

    public function loadWithSave($key, \Closure $closure, $id = null)
    {
        $r = $this->loadCache($key);
        if ($r) {
            return $r;
        } else {
            return $this->saveCache($key, $closure(), $id);
        }
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function loadCache($key)
    {
        return $this->cache->load($key);
    }

    /**
     * @param $key
     * @param $data
     *
     * @return mixed
     */
    public function saveCache($key, $data, $id = null)
    {
        $this->cache->save($key, $data, [
            Cache::TAGS => $this->getTags($id)
        ]);

        return $data;
    }
}