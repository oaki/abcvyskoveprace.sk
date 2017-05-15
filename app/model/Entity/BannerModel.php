<?php
namespace App\Model\Entity;

use App\Model\BaseDbModel;
use App\Model\Entity\File\FileNodeModel;
use Nette\Caching\Storages\FileStorage;

class BannerModel extends BaseDbModel
{

    protected $table = 'banner';

    public $fileNodeModel;

    public function __construct(\DibiConnection $connection, FileStorage $fileStorage, FileNodeModel $fileNodeModel)
    {
        parent::__construct($connection, $fileStorage);

        $this->fileNodeModel = $fileNodeModel;
    }

    function getActiveBanners()
    {
        $list = $this->getFluent()
            ->where('is_active = 1')
            ->orderBy('order')
            ->fetchAll();

        foreach ($list as $k => $l) {
            $list[$k]['images'] = $this->fileNodeModel->getFiles($this->fileNodeModel->getIdFileNode($l['id_banner'], 'Banner'));

        }

        return $list;
    }
}
