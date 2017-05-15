<?php
/**
 * Created by PhpStorm.
 * User: pavolbincik
 * Date: 7/23/15
 * Time: 10:18 PM
 */

namespace App\FrontModule\components\OrderSummaryControl;

use App\Model\ArrayAccessTrait;

use App\Model\Entity\Eshop\VatModel;

use App\Model\Entity\Eshop\ProductModel;

use App\Model\OrderUserData;
use App\Model\Payment;
use App\Model\Shipping;
use App\Model\Vat;
use Nette\Http\Session;
use Nette\Object;
use Oaki\Cart\Cart;

class OrderSummary extends Object implements \ArrayAccess
{

    use ArrayAccessTrait;

    /**
     * @var Shipping
     */
    private $shipping;

    /**
     * @var Payment
     */
    private $payment;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var \App\Model\Entity\Eshop\ProductModel
     */
    private $productModel;

    /**
     * @var VatModel
     */
    private $vat;

    /**
     * @var OrderUserData
     */
    private $userData;

    /**
     * OrderSummaryModel constructor.
     *
     * @param Session $session
     * @param Cart    $cart
     */
    public function __construct(Session $session, Cart $cart, ProductModel $productModel, Shipping $shipping, Payment $payment, VatModel $vat)
    {
        $this->data = $session->getSection(__CLASS__);
        $this->cart = $cart;

        $this->shipping     = $shipping;
        $this->payment      = $payment;
        $this->productModel = $productModel;
        $this->vat          = $vat;
        $this->userData     = new OrderUserData($session->getSection('userInfo'));

        if ($this['shipping'] == null) {
            $this['shipping'] = $this->getShipping()->getDefaultShipping();
        }

        if ($this['payment'] == null) {
            $this['payment'] = $this->getPayment()->getDefaultPayment();
        }
    }

    /**
     * @return Shipping
     */
    public function getShipping(): Shipping
    {
        return $this->shipping;
    }

    /**
     * @param Shipping $shipping
     */
    public function setShipping(Shipping $shipping)
    {
        $this->shipping = $shipping;
    }

    /**
     * @return Payment
     */
    public function getPayment(): Payment
    {
        return $this->payment;
    }

    /**
     * @param Payment $payment
     */
    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function reset()
    {
        $this->cart->clear();

        $this['comment'] = '';
        $this['logo']    = '';
        $this['step']    = 0;
    }

    public function getTotalPrice()
    {
        // cart
        $cartPrice = $this->getCart()->getTotalPrice();

        $shippingPrice = $this->getShippingPrice()->price;

        return $cartPrice + $shippingPrice;
    }

    public function getTotalPriceWithVat()
    {
        $cartPrice = $this->getCart()->getTotalPriceWithTax();

        $shippingPrice = $this->getShippingPrice()->priceWithTax;

        return $cartPrice + $shippingPrice;
    }

    public function update($values)
    {
        foreach ($values as $k => $v) {
            $this->data[$k] = $v;
        }

        return $this;
    }

    public function getCheckedShipping()
    {
        $checkedShipping          = $this->getShipping()->get($this['shipping']);
        $checkedShipping['price'] = $this->getShippingPrice();

        return $checkedShipping;
    }

    public function getCheckedPayment()
    {
        return $this->getPayment()->get($this->getCheckedPaymentKey());
    }

    public function getCheckedShippingKey()
    {
        return $this['shipping'];
    }

    public function getShippingAddress()
    {
        return $this['shippingAddress'];
    }

    public function getCheckedPaymentKey()
    {
        return $this['payment'];
    }

    public function getShippingPrice()
    {
        return $this->getShipping()->getShippingPrice($this['shipping']);
    }

    public function updateCartPricesFromDb()
    {
        $items = $this->getCart()->getItems();
        foreach ($items as $key => $item) {
            try {
                $p = $this->productModel->getAllInfo($item->getId(), false, false);
                $item->setPriceModel($p['price']);
            } catch (\Exception $e) {
                // if product does not exist remove it
                $this->getCart()->delete($key);
            }
        }
    }

    /**
     * @return OrderUserData
     */
    public function getUserData()
    {
        return $this->userData;
    }

    public function getComment()
    {
        return $this['comment'];
    }

    public function setStep($step)
    {
        $this['step'] = (int)$step;
    }

    public function getStep()
    {
        return (int)$this['step'];
    }

    /**
     * @return Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

}