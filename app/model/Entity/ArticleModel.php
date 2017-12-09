<?php
namespace App\Model\Entity;

use App\Model\BaseDbModel;
use App\Model\Entity\File\FileNodeModel;
use Nette\Caching\Storages\FileStorage;
use Tracy\Debugger;

class ArticleModel extends BaseDbModel implements IBaseCms// object je Nette\Object
{

    private $fileNodeService;

    function __construct(\DibiConnection $connection, FileStorage $cache, FileNodeModel $fileNodeService)
    {
        parent::__construct($connection, $cache);
        $this->table = 'article';
        $this->id_name = 'id_node';
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


        $key = 'articleSlugToId(' . $slug . ')';

        $id = $this->loadCache($key);

        if ($id) {
            return $id;
        } else {

            /*
            // check if page model has slug
            $pageSlug = explode('/', $slug);

            if (count($pageSlug) > 1) {
                // Deleting last array item
                $articleSlug = array_pop($pageSlug);
                $pageSlug = implode('/', $pageSlug);

                $id_menu_item = $pageModel->getFluent()->removeClause('select')->select('id_menu_item')->where("slug LIKE %s", $pageSlug)->fetchSingle();
                if ($id_menu_item) {
                    $id = $this->getFluent()->removeClause('select')->select('id_node')->where("slug LIKE %s", $articleSlug)->fetchSingle();
                }
            }
            */
            $id = $this->getFluent()->removeClause('select')->select('id_node')->where("slug LIKE %s", $slug)->fetchSingle();

            if (!$id) $id = null;
        }

        return $this->saveCache($key, $id);
    }

    public function idToSlug($id)
    {
        $key = 'articleIdToSlug(' . $id . ')';
        $slug = $this->loadCache($key);


        if ($slug) {
            return $slug;
        } else {


            /*
            $id_menu_item = $pageModel->getFluent()->removeClause('select')->select('id_menu_item')->join('node')->using('(id_menu_item)')->where("id_node = %i", $id)->fetchSingle();

            if ($id_menu_item) {
                $pageSlug = $pageModel->idToSlug($id_menu_item);

                $name = $this->getFluent()->removeClause('select')->select('slug')->where("id_node = %i", $id)->fetchSingle();

                $name = $pageSlug.'/'.$name;
            }

            */
            $name = $this->getFluent()->removeClause('select')->select('slug')->where("id_node = %i", $id)->fetchSingle();

            if (!$name) $name = null;
        }

        return $this->saveCache($key, $name);
    }

}