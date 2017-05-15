<?php
/**
 * Created by PhpStorm.
 * User: pavolbincik
 * Date: 7/27/15
 * Time: 11:57 PM
 */

namespace App\Model;

use App\Model\Entity\Eshop\DeliveryModel;
use Nette\Object;
use Oaki\Cart\Cart;

class Shipping extends Object
{

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var DeliveryModel
     */
    private $deliveryModel;

    /**
     * Shipping constructor.
     *
     * @param Cart  $cart
     * @param array $params
     */
    public function __construct(DeliveryModel $deliveryModel, Cart $cart)
    {
        $this->cart          = $cart;
        $this->deliveryModel = $deliveryModel;
    }

    public function get($key)
    {
        return $this->deliveryModel->fetch($key);
    }

    public function getShippingPrice($id_delivery)
    {
        return $this->deliveryModel->getDeliveryWithPrice($id_delivery, $returnOnlyPrice = true);
    }

    public function getDefaultShipping()
    {
        return $this->deliveryModel->getDefault();
    }

    public function getShippingOptions($assoc = false)
    {
        if ($assoc) {
            return $this->deliveryModel->getFluent()->fetchPairs('id_delivery', 'name');
        }

        $list = $this->deliveryModel->getFluent('id_delivery')->fetchAll();

        foreach ($list as $k => $l) {
            $list[$k] = $this->deliveryModel->getDeliveryWithPrice($l['id_delivery']);
        }

        return $list;

    }

}