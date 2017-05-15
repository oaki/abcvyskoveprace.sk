<?php

namespace App\Model\Entity\Eshop;

use App\Model\BaseDbModel;
use App\Model\Entity\SettingModel;
use App\Model\PriceModel;
use Nette\Caching\Storages\FileStorage;

class DeliveryModel extends BaseDbModel
{

    protected $table = 'delivery';

    private $deliveryTax = 0;

    private $settingModel = 0;

    public function __construct(SettingModel $settingModel, \DibiConnection $connection, FileStorage $fileStorage)
    {
        parent::__construct($connection, $fileStorage);

        $this->settingModel = $settingModel;
        $this->deliveryTax  = $this->settingModel->getValueByName('DELIVERY_TAX');
    }

    //cena aj s dph aj bez dph
    function getDeliveryWithPrice($id, $returnOnlyPrice = false)
    {

        $list        = $this->fetch($id);
        $list['tax'] = $this->deliveryTax;
        $priceModel  = new PriceModel();
        $priceModel->setFromDeliveryDb($list);
        $list['price'] = $priceModel;

        return $returnOnlyPrice ? $priceModel : $list;
    }

    function getDefault()
    {
        return $this->getFluent()->removeClause('select')->select('id_delivery')->where('[default] = 1')->fetchSingle();
    }

    function repairSequence()
    {
        $list    = $this->fetchAll();
        $counter = 1;
        foreach ($list as $l) {
            $this->update(array('sequence' => ++$counter), $l['id_delivery']);
        }
    }
}