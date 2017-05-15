<?php

namespace App\Model\Entity;

use App\Model\BaseDbModel;
use App\Model\Entity\File\FileNodeModel;
use Nette\Caching\Storages\FileStorage;

class ContactModel extends BaseDbModel implements IBaseCms, IJoinFileNodeModel// object je Nette\Object
{

    protected $table = 'contact';

    protected $id_name = 'id_node';

    private $fileNodeService;

    function __construct(\DibiConnection $connection, FileStorage $fileStorage, FileNodeModel $fileNodeService)
    {
        parent::__construct($connection, $fileStorage);
        $this->table           = 'contact';
        $this->id_name         = 'id_node';
        $this->fileNodeService = $fileNodeService;
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
        $desc = 'Nedefinovane';

        if (!isset($values['title']) OR $values['title'] == '')
            $values['title'] = 'Contact';

        return array('node_name' => $values['title'], 'node_desc' => $desc);
    }

    function getIdFileNode($id)
    {
        return $this->fileNodeService->getIdFileNode($id, 'Slideshow');
    }

}