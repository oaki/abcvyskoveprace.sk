<?php
namespace App\Model\Entity\Eshop;

use App\Model\BaseDbModel;
use App\Model\Entity\File\FileNodeModel;
use App\Model\Entity\IJoinFileNodeModel;
use DibiConnection;
use Nette\Caching\Storages\FileStorage;

class CategoryModel extends BaseDbModel implements IJoinFileNodeModel
{

    protected $categoryLangModel;

    protected $fileNodeService;

    protected $table = 'category';

    function __construct(DibiConnection $connection, FileStorage $cache, CategoryLangModel $categoryLangModel, FileNodeModel $fileNodeService)
    {
        parent::__construct($connection, $cache);
        $this->categoryLangModel = $categoryLangModel;
        $this->fileNodeService   = $fileNodeService;
//		$this->categoryLangModel->repairAllLinkRewrite();
    }

    public function getChildren($id_parent, $id_lang)
    {
        $list = $this->getFluent('id_category')
            ->where('id_parent = %i', $id_parent, "AND status = 'live'")
            ->orderBy('sequence')
            ->fetchAll();
        foreach ($list as $k => $l) {
            $list[$k] = $this->get($l['id_category'], $id_lang);
        }

        return $list;
    }

    function get($id, $id_lang)
    {
        $key = 'get(' . $id . ', ' . $id_lang . ')';
        $r   = $this->loadCache($key);
        if ($r) {
            return $r;
        }

        $c = $this->fetch($id);

        if (!$c) {
            return false;
        }
        $c['files'] = $this->getFiles($id);

        $c['first_image'] = [
            'image' => 'no-image.jpg'
        ];

        if (count($c['files']) > 0) {
            $c['first_image'] = $c['files'][0];
        }

        $lang = $c['category_lang'];
        unset($c['category_lang']);

        foreach ($lang[$id_lang] as $k => $i) {
            $c[$k] = $i;
        }

        return $this->saveCache($key, $c);
    }

    function fetch($id)
    {
        $f = parent::fetch($id);

        if (!$f) {
            return false;
        }
        $f['category_lang'] = $this->categoryLangModel->fetchAssoc($id);

        return $f;
    }

    private function getFiles($id)
    {
        $l = $this->fileNodeService->getFiles($this->getIdFileNode($id));

        return $l;
    }

    function getIdFileNode($id)
    {
        return $this->fileNodeService->getIdFileNode($id, 'Category');
    }

    public function getCategoriesForMenu($id_lang)
    {
        return $this->loadWithSave('getCategoriesForMenu(' . $id_lang . ')', function () use ($id_lang) {
            return $this->getFluent()
                ->join('category_lang')->on('category.id_category = category_lang.id_category AND id_lang = %i', $id_lang)
                ->where("show_in_menu = '1'")->fetchAll();
        });

    }

    public function getBreadcrumbs($id_category, $id_lang)
    {
        $row = $this->getFluent()
            ->join('category_lang')->on('category_lang.id_category = category.id_category AND id_lang = %i', $id_lang)
            ->where('category.id_category = %i', $id_category)->fetch($id_category);

        $list = $this->getFluent()
            ->join('category_lang')->on('category_lang.id_category = category.id_category AND id_lang = %i', $id_lang)
            ->where('[left] < %i', $row['left'], 'AND [right] > %i', $row['right'])->orderBy('left')->fetchAll();
        $arr  = [];

        foreach ($list as $l) {
            $arr[] = $l;
        }
        $arr[] = $row;
//        dump($arr);
//        exit;
        return $arr;
//        $result1 = mysql_query("SELECT * FROM strom WHERE lft < $row[lft] AND rgt > $row[rgt] ORDER BY lft");
//        while ($row1 = mysql_fetch_assoc($result1)) {
//            echo "<a href='?id=$row1[id]'>" . htmlspecialchars($row1["nadpis"]) . "</a> &gt; ";
//        }
//        mysql_free_result($result1);
    }

    public function add($values)
    {
        $categoryRows       = $this->getTableRows();
        $categoryNamesRows  = $this->categoryLangModel->getTableRows();
        $categoryValues     = [];
        $categoryLangValues = [];

        foreach ($values as $k => $v) {
            if (isset($categoryRows[$k])) {
                $categoryValues[$k] = $v;
            }

            if (isset($categoryNamesRows[$k])) {
                $categoryLangValues[$k] = $v;
            }
        }

        $id_category = $this->insertAndReturnLastId($categoryValues);
        $this->categoryLangModel->insert($categoryLangValues, $values['id_lang'], $id_category);
    }

    public function getIdByName($name, $id_parent = null)
    {
        return $this->getFluent('id_category')->join('category_lang')->using('(id_category)')
            ->where('name = %s', $name, '%if', $id_parent !== null, 'AND id_parent = %i', $id_parent)->fetchSingle();
    }

    function getDefaultIdCategory()
    {
        return $this->getFluent('id_category')->where('status = "live" AND id_parent IS NULL')->orderBy('sequence')->fetchSingle();
    }

    function update($values, $id)
    {

        if (isset($values['category_lang'])) {

            foreach ($values['category_lang'] as $category_lang) {
                $this->categoryLangModel->updateOrInsert($category_lang, $category_lang['id_lang'], $id);
            }
            unset($values['category_lang']);
        }
        parent::update($values, $id);

        $this->putInToTheSystem($id);
    }

    function putInToTheSystem($id_category)
    {
        //over ci je status not_in_system, ak je prepis na live
        $l = $this->getFluent('1')->where("id_category = %i", $id_category, "AND status = 'not_in_system'")->fetchSingle();
        if ($l) {
            $this->update(array('status' => 'live'), $id_category);
        }
    }

    function getParents($id)
    {
        $parents     = array();
        $parents[]   = $id;
        $last_parent = $id;
        $_tmp_count  = 0;
        while (($last_parent = $this->getParent($last_parent)) != null AND $_tmp_count < 10) {
            ++$_tmp_count;

            $parents[] = $last_parent;
        }

        return $parents;
    }

    function getParent($id)
    {
        return $this->getFluent('id_parent')->where('id_category = %i', $id)->fetchSingle();
    }

    /*
     * Vymazanie zo systemu moduly ktore neboli ulozene
     */

    function deleteUnsaveCategory()
    {
        //vymaze az po 12 hodinach ak sa nezmeni stav
        $list = $this->getFluent()->where("status = 'not_in_system' AND add_date < ( NOW() - 60*12 )")->fetchAll();

        if (!empty($list)) {
            foreach ($list as $l) {
                $this->delete($l['id_category']);
            }
        }
    }

    function getTree($id_lang, $rows = '*')
    {
        $key = 'getTree(' . $id_lang . ',' . $rows . ')';

        if ($tree = $this->loadCache($key)) {
            return $tree;
        } else {
            $tree = $this->getFluent($rows)
                ->join('category_lang')->using('(id_category)')
                ->where('id_lang = %i', $id_lang, 'AND status != "not_in_system"')
                ->orderBy(array('sequence' => 'ASC'))
                ->fetchAssoc('id_parent,id_category');

            return $this->saveCache($key, $tree);
        }

    }

    function fetchPairsForSelect($id_lang)
    {
        $tree = $this->getFluent('id_category,name,depth')
            ->join('category_lang')->using('(id_category)')
            ->where('id_lang = %i', $id_lang, 'AND status != "not_in_system"')
            ->orderBy(array('left' => 'ASC'))
            ->fetchAssoc('id_category');

        $r = array();
        foreach ($tree as $k => $l) {
            $r[$k] = \Nette\Utils\Strings::indent($l->name, $l->depth - 1, '---');
        }

        return $r;
    }

    /**
     * @return CategoryLangModel
     */
    public function getCategoryLangModel(): CategoryLangModel
    {
        return $this->categoryLangModel;
    }

    public function idToSlug($id, $idLang)
    {
        $key = 'idToSluglistCategory(' . $idLang . ')';

        if ($list = $this->loadCache($key)) {

        } else {

            $list = $this->connection->query('SELECT id_category,slug FROM [category_lang] JOIN [category] USING(id_category) WHERE status !="not_in_system" AND id_lang = %i', $idLang)->fetchPairs('id_category', 'slug');
            $this->saveCache($key, $list);
        }

        if (isset($list[$id])) {
            return $list[$id];
        }

        return null;
    }

    public function slugToId($slug, $idLang)
    {
        $key = 'slugToIdlistCategory(' . $idLang . ')';

        if ($list = $this->loadCache($key)) {

        } else {
            $list = $this->connection->query('SELECT id_category,slug FROM [category_lang] JOIN [category] USING(id_category) WHERE status !="not_in_system" AND id_lang = %i', $idLang)->fetchPairs('slug', 'id_category');
            $this->saveCache($key, $list);
        }

        if (isset($list[$slug])) {
            return $list[$slug];
        }

        return null;
    }

}