<?php

class CadastralControl extends BaseControl
{

    private $county, $district;

    function setDefaults($arr)
    {
        $this->county   = $arr['county'];
        $this->district = $arr['district'];
    }

    function render()
    {
        $t = $this->template;

        $t->counties = $this->getService('City')->getCounty();

        $t->setFile(dirname(__FILE__) . '/default.latte');
        $t->render();
    }

    function handleGetCatastral()
    {
        dde($_GET);
    }
}