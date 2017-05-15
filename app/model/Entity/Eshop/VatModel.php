<?php
namespace App\Model\Entity\Eshop;

use App\Model\BaseDbModel;

class VatModel extends BaseDbModel
{

    protected $table = 'vat';

    function getDefault()
    {
        return $this->getFluent('id_vat')->where('is_default = 1')->fetchSingle();
    }

    public function getCoef($id_vat)
    {
        /**
         * @todo
         */
        return 1 + $this->getFluent('value')->where('id_vat = %i', $id_vat)->fetchSingle() / 100;
    }
}