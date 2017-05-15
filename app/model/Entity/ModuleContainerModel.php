<?php

namespace App\Model\Entity;

use App\Model\BaseDbModel;
use Nette\Caching\Storages\FileStorage;

class ModuleContainerModel extends BaseDbModel
{

    function __construct(\DibiConnection $connection, FileStorage $cache)
    {
        parent::__construct($connection, $cache);
        $this->table      = 'type_module';
        $this->convention = 'id_type_module';
    }
}
