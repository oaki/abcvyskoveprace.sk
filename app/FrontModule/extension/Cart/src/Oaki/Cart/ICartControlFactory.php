<?php

namespace Oaki\Cart;

interface ICartControlFactory
{

    /**
     * @return CartControl
     */
    function create();

}