<?php
namespace App\Model\Entity\Eshop;

use App\Model\BaseDbModel;
use Nette\Caching\Storages\FileStorage;

class OrderModel extends BaseDbModel
{

    public $orderProductModel;

    public $paymentModel;

    public $deliveryModel;

    protected $table = 'order';

    static public $states = [
        0 => 'Neobjednané',
        1 => 'Odoslané',
        2 => 'Pripravujeme',
        3 => 'Pripravené na osobný odber',
    ];

    public function __construct(\DibiConnection $connection, FileStorage $fileStorage,
                                OrderProductModel $orderProductModel, PaymentModel $paymentModel, DeliveryModel $deliveryModel
    )
    {
        parent::__construct($connection, $fileStorage);
        $this->orderProductModel = $orderProductModel;
        $this->paymentModel      = $paymentModel;
        $this->deliveryModel     = $deliveryModel;
    }

    public function getAllInfo($id_order)
    {
        $l = $this->fetch($id_order);

        $l['products'] = $this->getOrderProductModel()->getFluent()->where('id_order = %i', $id_order)->fetchAll();

        $l['delivery_price_with_tax'] = $l['delivery_price'] * (1 + $l['delivery_tax'] / 100);
        $l['payment_price_with_tax']  = $l['payment_price'] * (1 + $l['payment_tax'] / 100);

        return $l;

    }

    /**
     * @return OrderProductModel
     */
    public function getOrderProductModel(): OrderProductModel
    {
        return $this->orderProductModel;
    }

}