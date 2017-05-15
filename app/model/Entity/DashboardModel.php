<?php
namespace App\Model\Entity;

use App\Model\BaseDbModel;
use Nette\Caching\Storages\FileStorage;

class DashboardModel extends BaseDbModel
{

    public function __construct(\DibiConnection $connection, FileStorage $fileStorage)
    {
        parent::__construct($connection, $fileStorage);
        $this->table = 'dashboard';
    }

}