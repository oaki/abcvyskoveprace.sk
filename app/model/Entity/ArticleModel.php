<?php
namespace App\Model\Entity;

use App\Model\BaseDbModel;
use App\Model\Entity\File\FileNodeModel;
use Nette\Caching\Storages\FileStorage;

class ArticleModel extends BaseDbModel implements IBaseCms, IModuleSlug// object je Nette\Object
{

    private $fileNodeService;

    function __construct(\DibiConnection $connection, FileStorage $cache, FileNodeModel $fileNodeService)
    {
        parent::__construct($connection, $cache);
        $this->table           = 'article';
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
        return array('node_name' => $values['title'], 'node_desc' => $values['text']);
    }

    function get($id_node)
    {
        $a = $this->getFluent()->select("DATE_FORMAT(add_date, '%d.%m.%Y') AS add_date_formated")->where("id_node=%i", $id_node)->fetch();
        if (!$a)
            return false;

        $a['files'] = $this->fileNodeService->getFiles($this->fileNodeService->getIdFileNode($id_node, 'Article'));

        $a['first_img'] = (isset($a['files'][0])) ? $a['files'][0] : array('src' => 'no-image', 'ext' => 'jpg');

        return $a;
    }

    public function slugToId($slug)
    {
        $slug = rtrim($slug, '/');
        $key  = 'slugToId(' . $slug . ')';

        $id = $this->loadCache($key);

        if ($id) {
            return $id;
        } else {
            $id = $this->getFluent()->removeClause('select')->select('id_node')->where("slug LIKE %s", $slug)->fetchSingle();
            if (!$id) $id = null;
        }

        return $this->saveCache($key, $id);
    }

    public function idToSlug($id)
    {
        $key  = 'idToSlug(' . $id . ')';
        $slug = $this->loadCache($key);

        if ($slug AND 1 == 2) {
            return $slug;
        } else {
            $name = $this->getFluent()->removeClause('select')->select('slug')->where("id_node = %i", $id)->fetchSingle();

            if (!$name) $name = null;
        }

        return $this->saveCache($key, $name);
    }

}