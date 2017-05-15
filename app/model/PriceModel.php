<?php
namespace App\Model;

use Nette\Object;

class PriceModel extends Object
{

    public $originalPrice;

    public $salePercent;

    public $isSale;

    public $price;

    public $tax;

    public $idProduct;

    public $userDiscount;

    public $priceWithTax;

    public $taxPrice;

    public $priceToShow;

    public $originalPriceToShow;

    public $user;

    public function __construct($userDiscount = 0)
    {
        $this->userDiscount = $userDiscount;
    }

    public function setFromProductDb($arr)
    {
        $this->originalPrice = $arr['original_price'];
        $this->salePercent   = $arr['sale_percent'];
        $this->isSale        = $arr['sale'];
        $this->price         = $arr['price'];
        $this->tax           = $arr['tax'];
        $this->idProduct     = $arr['id_product'];
        $this->recalculate();
    }

    public function setFromDeliveryDb($arr)
    {

//        var_dump($arr);
//        exit;
        $this->originalPrice = isset($arr['price']) ? $arr['price'] : 0;
        $this->salePercent   = 0;
        $this->isSale        = 0;
        $this->price         = isset($arr['price']) ? $arr['price'] : 0;
        $this->tax           = isset($arr['tax']) ? $arr['tax'] : 0;
        $this->idProduct     = null;
        $this->recalculate();
    }

    public function recalculate()
    {
        if ($this->originalPrice == $this->price && $this->userDiscount) {
            $this->price = $this->price / (1 + $this->userDiscount / 100);
        }

        $this->priceWithTax = (1 + $this->tax / 100) * $this->price;
        $this->taxPrice     = $this->priceWithTax - $this->price;

        $taxCoef = 1 + $this->tax / 100;

        $this->priceToShow         = $taxCoef * $this->price;
        $this->originalPriceToShow = $taxCoef * $this->originalPrice;
    }
}