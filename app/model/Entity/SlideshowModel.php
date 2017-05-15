<?php
namespace App\Model\Entity;

use App\Model\BaseDbModel;
use App\Model\Entity\File\FileNodeModel;
use Nette\Caching\Storages\FileStorage;
use Nette\Utils\Strings;

class SlideshowModel extends BaseDbModel implements IBaseCms, IJoinFileNodeModel// object je Nette\Object
{

    private $fileNodeService;

    function __construct(\DibiConnection $connection, FileStorage $cache, FileNodeModel $fileNodeService)
    {
        parent::__construct($connection, $cache);
        $this->table           = 'slideshow';
        $this->id_name         = 'id_node';
        $this->fileNodeService = $fileNodeService;
    }

    function get($id_node)
    {
        $a = $this->fetch($id_node);
        if (!$a)
            return false;

        $a['files'] = $this->fileNodeService->getFiles($this->getIdFileNode($id_node));

        $a['first_img'] = (isset($a['files'][0])) ? $a['files'][0] : array('src' => 'no-image', 'ext' => 'jpg');

        return $a;
    }

    /*
     * admin
     */
    function addModuleToCms($id_node)
    {
        $this->insert(array('id_node' => $id_node), false);
    }

    function identifyTitleAndDescriptionForNode($values)
    {
        $desc = 'Počet súborov: ' . $this->fileNodeService->getCountFiles($this->getIdFileNode($values['id_node']));
        if (!isset($values['title']) OR $values['title'] == '')
            $values['title'] = 'Prezentácia';

        return array('node_name' => $values['title'], 'node_desc' => $desc);
    }

    function getIdFileNode($id)
    {
        return $this->fileNodeService->getIdFileNode($id, 'Slideshow');
    }

}