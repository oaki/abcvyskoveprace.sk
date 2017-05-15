<?php

namespace App\Model\Entity\Eshop;

use App\Model\BaseDbModel;
use App\Model\PriceModel;

use Nette\Application\BadRequestException;
use Nette\Caching\Storages\FileStorage;
use Nette\Security\User;

class ProductParamModel extends BaseDbModel
{

    public $user;

    protected $table = 'product_param';

    public function __construct(\DibiConnection $connection, FileStorage $fileStorage, User $user)
    {
        parent::__construct($connection, $fileStorage);
        $this->user = $user;
    }

    public function getFluentWithProduct($select = '*')
    {
        return $this->getFluent($select)
            ->join('product')
            ->on('product_param.id_product = product.id_product')
            ->where('is_main = 1');
    }

    public function fetchByProductId($id_product, $id_lang)
    {
        $q = $this->getQuery($id_lang)
            ->where('product.id_product = %i', $id_product);

        $l = (clone $q)->where('is_main = 1')
            ->fetch();

        if (!$l) {
            $l = $q->orderBy('price')->fetch();

            $this->update(['is_main' => 1], $l['id_product_param']);
        }

        return $l;
    }

    public function getQuery($id_lang)
    {
        return $this->getFluent('
            product.*,
            product_lang.*,
            product_param.*,
            product_mark.name AS product_mark_name')
            ->join('product')->on('product.id_product = product_param.id_product')
            ->join('product_lang')->on('product.id_product = product_lang.id_product')
            ->leftJoin('product_mark')->using('(id_product_mark)')
            ->where('id_lang = %i', $id_lang);
    }

    public function fetchFull($id_product_param, $id_lang)
    {
        return $this->getQuery($id_lang)->where('id_product_param = %i', $id_product_param)->fetch();
    }

    public function getIdByCode($code)
    {
        return $this->getFluent('id_product_param')->where('code = %s', $code)->fetchSingle();
    }

    public function getPrice($id_product_param)
    {
        $param = $this->connection->fetch("
			SELECT 
				price AS original_price, 
				sale_percent, 
				sale, 
				IF( sale = 1, price - (price/100*sale_percent), price) AS price,
				vat.value AS tax,
				id_product
			FROM 
				product_param 
				JOIN product USING(id_product) 
				JOIN vat USING(id_vat)
			WHERE 
				id_product_param = %i", $id_product_param);

        if (!$param)
            throw new BadRequestException('ID product param neexistuje.');
        //ak je user_discount > 0 a product nieje v akcii pouzije sa uzivatelska zlava

        $userDiscount = ($this->user->isLoggedIn() && $this->user->getIdentity()->discount) ? $this->user->getIdentity()->discount : 0;
        $priceModel   = new PriceModel($userDiscount);
        $priceModel->setFromProductDb($param);

        return $priceModel;
    }

    public function idToSlug($id, $idLang)
    {
        $slug = $this->connection->fetchSingle('SELECT slug FROM [product_param_rewrite] WHERE id_product_param = %i', $id);
        if ($slug) {
            return $slug;
        }

        return null;
    }

    public function slugToId($slug, $idLang)
    {
        $id = $this->connection->fetchSingle('SELECT id_product_param FROM [product_param_rewrite] WHERE slug = %s', $slug);
        if ($id) {
            return $id;
        }

        return null;
    }
}