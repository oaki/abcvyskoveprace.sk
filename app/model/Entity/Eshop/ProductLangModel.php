<?php

namespace App\Model\Entity\Eshop;

use App\Model\BaseDbModel;

class ProductLangModel extends BaseDbModel
{

    protected $table = 'product_lang';

    public function update($values, $where = [])
    {
        $this->connection->update($this->table, $values)->where($where)->execute();
    }
}
//namespace App\Model\Entity\Eshop;
//
//use DibiConnection;
//
//class ProductLangModel{
//
//	private $db;
//
//	function __construct(DibiConnection $connection) {
//		$this->db = $connection;
//	}
//
//	function updateOrInsert($values, $id_lang, $id_product){
//
//		if($this->isExist($id_product, $id_lang)){
//			$this->update($values, $id_lang, $id_product);
//		}else{
//
//			$this->insert($values, $id_lang,$id_product);
//		}
//	}
//
//	function isExist($id_product, $id_lang){
//		return $this->db->fetchSingle("SELECT 1 FROM [product_lang] WHERE id_product = %i", $id_product,"AND id_lang = %i", $id_lang);
//	}
//
//	function insert($values, $id_lang, $id_product){
//		$values['id_lang'] = $id_lang;
//		$values['id_product'] = $id_product;
//		$this->db->insert('product_lang', $values)->execute();
//	}
//
//	function update($values, $id_lang, $id_product){
//		$this->db->update('product_lang', $values)->where('id_lang = %i',$id_lang,'AND id_product = %i',$id_product)->execute();
//	}
//
//	function delete($id_lang, $id_product){
//		$this->db->delete('product_lang')->where('id_lang = %i',$id_lang,'AND id_product = %i',$id_product)->execute();
//	}
//
//	function fetchAssoc($id_product){
//		return $this->db->query("SELECT * FROM [product_lang] WHERE id_product = %i", $id_product)->fetchAssoc('id_lang');
//	}
//}