<?php

namespace App\FrontModule\components\OrderSummaryControl;

interface IOrderSummaryFactory
{

    /**
     * @return OrderSummaryControl
     */
    function create();

}