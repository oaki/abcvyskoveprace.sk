<?php

namespace App\Model\Entity\Eshop;

use App\Model\BaseDbModel;
use DibiConnection;
use Nette\Utils\Strings;

class CategoryLangModel extends BaseDbModel
{

    protected $table = 'category_lang';

    function updateOrInsert($values, $id_lang, $id_category)
    {

        if (isset($values['link_rewrite']) AND $values['link_rewrite'] == '') {
            $values['link_rewrite'] = $values['name'];
        }
        if (isset($values['link_rewrite'])) {
            $values['link_rewrite'] = Strings::webalize($values['link_rewrite']);
        }

        if (isset($values['meta_title']) AND $values['meta_title'] == '') {
            $values['meta_title'] = $values['name'];
        }

        if ($this->isExist($id_category, $id_lang)) {
            $this->update($values, $id_lang, $id_category);
        } else {

            $this->insert($values, $id_lang, $id_category);
        }
    }

    function isExist($id_category, $id_lang)
    {
        return $this->connection->fetchSingle("SELECT 1 FROM [category_lang] WHERE id_category = %i", $id_category, "AND id_lang = %i", $id_lang);
    }

    function insert($values, $id_lang = null, $id_category = null)
    {
        $values['id_lang']     = $id_lang;
        $values['id_category'] = $id_category;
        $this->connection->insert('category_lang', $values)->execute();
    }

    function update($values, $id_lang = null, $id_category = null)
    {
        if (isset($values['link_rewrite']) AND $values['link_rewrite'] == '') {
            $values['link_rewrite'] = $values['name'];
        }
        if (isset($values['link_rewrite'])) {
            $values['link_rewrite'] = Strings::webalize($values['link_rewrite']);
        }

        if (isset($values['meta_title']) AND $values['meta_title'] == '') {
            $values['meta_title'] = $values['name'];
        }

        $this->connection->update('category_lang', $values)->where('id_lang = %i', $id_lang, 'AND id_category = %i', $id_category)->execute();
    }

    function delete($id_lang, $id_category = null)
    {
        $this->connection->delete('category_lang')->where('id_lang = %i', $id_lang, 'AND id_category = %i', $id_category)->execute();
    }

    function fetchAssoc($id_category)
    {
        return $this->connection->query("SELECT * FROM [category_lang] WHERE id_category = %i", $id_category)->fetchAssoc('id_lang');
    }

    function repairAllLinkRewrite()
    {

        $list = $this->connection->select('*')->from('category_lang')->fetchAll();
//		dde($list);
        foreach ($list as $l) {
            if ($l['link_rewrite'] == '') {
                if ($l['name'] != '') {
                    $this->connection->update('category_lang', array('link_rewrite' => Strings::webalize($l['name'])))
                        ->where('id_lang = %i', $l['id_lang'], 'AND id_category = %i', $l['id_category'])->execute();
                }
            }
        }
    }
}