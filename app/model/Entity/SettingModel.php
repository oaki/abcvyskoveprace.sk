<?php
namespace App\Model\Entity;

use App\Model\BaseDbModel;

class SettingModel extends BaseDbModel // object je Nette\Object
{

    protected $table = 'setting';

    public function findByName($name)
    {
        return $this->getFluent()->where('name = %s', $name)->fetch();
    }

    public function getValueByName($name)
    {
        return $this->getFluent('value')->where('name = %s', $name)->fetchSingle();
    }

    function fetchPairs()
    {
        $key  = 'fetchPairs()';
        $list = $this->loadCache($key);
        if ($list) {
            return $list;
        } else {
            $list = $this->getFluent()->fetchPairs('name', 'value');
        }

        return $this->saveCache($key, $list);
    }

}