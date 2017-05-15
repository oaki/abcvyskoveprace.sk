<?php
/**
 * Created by PhpStorm.
 * User: pavolbincik
 * Date: 7/27/15
 * Time: 11:57 PM
 */

namespace App\Model;

use App\Model\Entity\Eshop\PaymentModel;
use Nette\Object;
use Oaki\Cart\Cart;

class Payment extends Object
{

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var PaymentModel
     */
    private $paymentModel;

    /**
     * Shipping constructor.
     *
     * @param Cart  $cart
     * @param array $params
     */
    public function __construct(PaymentModel $paymentModel, Cart $cart)
    {
        $this->cart         = $cart;
        $this->paymentModel = $paymentModel;
    }

    public function get($key)
    {
        return $this->paymentModel->fetch($key);
    }

    public function getDefaultPayment()
    {
        return $this->paymentModel->getDefault();
    }

    public function getPaymentOptions($assoc = false)
    {
        if ($assoc) {
            return $this->paymentModel->getFluent()->fetchPairs('id_payment', 'name');
        } else {
            return $this->paymentModel->getFluent()->fetchAll();
        }

    }

}