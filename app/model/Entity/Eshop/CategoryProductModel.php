<?php

namespace App\Model\Entity\Eshop;

use App\Model\BaseDbModel;

class CategoryProductModel extends BaseDbModel
{

    protected $table = 'category_product';

    public function insertIfNotExist($values)
    {
        if (!$this->getFluent('1')->where($values)->fetchSingle()) {
            $this->insert($values);
        }
    }
}