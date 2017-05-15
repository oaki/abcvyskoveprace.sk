<?php

namespace App\Model\Entity\Eshop;

use App\Model\BaseDbModel;

class PaymentModel extends BaseDbModel
{

    protected $table = 'payment';

    function fetchAll()
    {
        return $this->getFluent()->orderBy('sequence')->fetchAll();
    }

    function fetchPairs($collum)
    {
        return $this->getFluent()->orderBy('sequence')->fetchPairs($this->getTableIdName(), $collum);
    }

    function getDefault()
    {
        return $this->getFluent('id_payment')->where('[is_default]= 1')->fetchSingle();
    }
//
//	//cena aj s dph aj bez dph
//	function getDeliveryWithPrice($id){
//
//		$list = $this->fetch($id);
//
//		$payment_vat = $this->context->parameters['PAYMENT_TAX'];
//
//		$list['price_array'] = array(
//				'price'=>$list['price'],
//				'tax_price'=>$list['price']/100*$payment_vat,
//				'price_with_tax'=>$list['price'] + ($list['price']/100*$payment_vat),
//				'tax'=>$payment_vat
//			);
//
//		return $list;
//	}

}