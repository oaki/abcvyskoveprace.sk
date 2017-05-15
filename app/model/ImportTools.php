<?php

namespace App\Model;

class ImportTools
{

    public static function arrayMapObjectToArray($object)
    {
        if (!is_object($object) && !is_array($object))
            return $object;

        $_tmp = (array)$object;
        if (empty($_tmp)) {
            return "";
        }

        return array_map('\App\Model\ImportTools::arrayMapObjectToArray', $_tmp);
    }

}