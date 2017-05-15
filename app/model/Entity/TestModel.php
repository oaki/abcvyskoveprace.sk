<?
namespace App\Model\Entity;

use Nette\Caching\Storages\FileStorage;
use Nette\DI\Container;
use Nette\Object;
use Nette\Utils\Strings;

class TestModel extends Object
{

    private $nodeService;

    function __construct(\DibiConnection $connection,
                         FileStorage $cache,
                         Container $context)
    {
        var_dump($connection);
        exit;
    }

    public function fetch($id_menu_item)
    {
        return $this->getFluent()->where('id_menu_item = %i', $id_menu_item)->fetch();
    }

    public function fetchAssoc($id_menu, $lang)
    {
        $key  = 'fetchAssoc(' . $id_menu . ',' . $lang . ')';
        $tree = $this->loadCache($key);

        if ($tree) {
            return $tree;
        } else {

            $tree = $this->getFluent()
                ->where('lang = %s', $lang, 'AND id_menu = %i', $id_menu)
                ->orderBy(array('sequence' => 'ASC'))
                ->fetchAssoc('parent_id,id_menu_item');
        }

        return $this->saveCache($key, $tree);
    }

    function getTreeUseInSelect($lang, $id_menu)
    {
        $tree   = $this->getTree($lang, $id_menu);
        $return = array(null => 'Root');

        function getSpace($count)
        {
            $string = '';
            for ($i = 1; $i < $count; ++$i) {
                $string .= '-';
            }

            return $string;
        }

        foreach ($tree as $t) {
            $return[$t['id_menu_item']] = getSpace($t['depth']) . $t['name'];
        }

        return $return;
    }

    function getTree($lang, $id_menu)
    {
        $key = 'tree_' . $lang . '_' . $id_menu;

        if ($tree = $this->loadCache($key)) {
            return $tree;
        } else {
            $tree = $this->getFluent()
                ->where(
                    "lang=%i", $lang, " AND
						id_menu=%i", $id_menu
                )
                ->orderBy('sequence')
                ->fetchAssoc('id_menu_item');

            return $this->saveCache($key, $tree);
        }
    }

    public function getParent($id_menu_item)
    {
        return $this->fetch($this->getFluent('parent_id')->where('id_menu_item = %i', $id_menu_item)->fetchSingle());
    }

    public function getParents($id_menu_item)
    {
        $key = 'getParent(' . $id_menu_item . ')';

        if ($parent = $this->loadCache($key)) {
            return $parent;
        } else {
            $all_item = $this->getFluent()->fetchAssoc('id_menu_item');

            $parent              = array();
            $actual_id_menu_item = $id_menu_item;
            if (!isset($all_item[$actual_id_menu_item]))
                return $parent;

            for ($i = 0; $i < 10; ++$i) {
                $parent[] = $all_item[$actual_id_menu_item];
                if ($all_item[$actual_id_menu_item]['parent_id'] == null)
                    break;

                $actual_id_menu_item = $all_item[$actual_id_menu_item]['parent_id'];
            }

            $parent = array_reverse($parent);
        }

        return $this->saveCache($key, $parent);

    }

    public function getChild($id_menu_item)
    {
        return $this->getFluent('id_menu_item')->where('parent_id = %i', $id_menu_item)->fetchAll();
    }

    function insert($values)
    {
        $values['add_date'] = new \DibiDateTime();

        //upravit parent na NULL ak je prazdne
        if ($values['parent_id'] == '') {
            $values['parent_id'] = null;
        }

        if (isset($values['slug']))
            $values['slug'] = Strings::webalize($values['slug']);
        parent::insert($values);
    }

    function update($values, $id)
    {
        if ($values['parent_id'] == '') {
            $values['parent_id'] = null;
        }

        if (isset($values['slug']))
            $values['slug'] = Strings::webalize($values['slug']);

        parent::update($values, $id);
    }

    function delete($id)
    {

        //vymazat vsetky podkategorie, i vsetky modely
        //zisti ci ma deti
        $child = $this->getChild($id);

        if ($child) {
            foreach ($child as $k => $l) {
                $nodes = $this->nodeService->getFluent()->where('id_menu_item = %i', $l['id_menu_item'])->fetchAll();
                foreach ($nodes as $node) {
                    $this->nodeService->delete($node->id_node);
                }
                $this->delete($l['id_menu_item']);
            }
        }

        $nodes = $this->nodeService->getFluent()->where('id_menu_item = %i', $id)->fetchAll();

        foreach ($nodes as $node) {
            $this->nodeService->delete($node->id_node);
        }

        //vymaze aj moduly pod
        parent::delete($id);
    }

    public function slugToId($slug)
    {
//		$slug = rtrim($slug, '/');
        $key = 'slugToId(' . $slug . ')';

        $id = $this->loadCache($key);

        if ($id) {
            return $id;
        } else {
            $url_items = explode("/", $slug);

            $id = null;

            $parent_id = null;

            foreach ($url_items as $url_item) {
                $id = $parent_id = $this->getFluent('id_menu_item')->where("%if", $parent_id === null, "parent_id IS NULL %else parent_id = %i", $parent_id, " %end AND slug LIKE %s", $url_item, 'AND status !="deactivate"')->fetchSingle();

                if (!$id) {
                    $id = null;
                    break;
                }
            }

        }

        return $this->saveCache($key, $id);
    }

    public function idToSlug($id)
    {

        $key = 'idToSlug(' . $id . ')';

        $slug = $this->loadCache($key);

        if ($slug) {
            return $slug;
        } else {
            $parents = $this->getParents($id);

            $slug = null;
            foreach ($parents as $parent) {
                $slug .= $parent['slug'] . '/';
            }

//			$name = $this->getFluent('slug')->where("id_menu_item = %i",$id)->fetchSingle();
//			if(!$slug) $slug = NULL;
        }

//		dde(trim($slug,'/'));
        $slug = trim($slug, '/');

        return $this->saveCache($key, $slug);

    }
}