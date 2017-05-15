<?php
namespace App\Model\Entity\Eshop;

use App\Model\BaseDbModel;
use App\Model\Entity\File\FileNodeModel;
use Nette\Caching\Storages\FileStorage;

class ProductMarkModel extends BaseDbModel
{

    protected $table = 'product_mark';

    private $fileNodeService;

    function __construct(\DibiConnection $connection, FileStorage $cache, FileNodeModel $fileNodeService)
    {
        parent::__construct($connection, $cache);
        $this->fileNodeService = $fileNodeService;
    }

    public function get($id)
    {
        $l         = $this->fetch($id);
        $l['file'] = $this->getFirstFile($id);

        return $l;
    }

    private function getFirstFile($id)
    {
        return $this->fileNodeService->getFile($this->fileNodeService->getIdFileNode($id, 'Mark'));
    }

    public function getTopMarks($limit)
    {
        return $this->loadWithSave('getTopMarks(' . $limit . ')', function () use ($limit) {
            $list = $this->getFluent()
                ->where('top = 1')
                ->limit($limit)
                ->fetchAll();

            foreach ($list as $k => $l) {
                $list[$k]['file'] = $this->getFirstFile($l['id_product_mark']);
            }

            return $list;
        });
    }

    public function getIdByName($name)
    {
        return $this->getFluent('id_product_mark')->where('name like %s', $name)->fetchSingle();
    }
}