<?php
/**
 * Created by PhpStorm.
 * User: pavolbincik
 * Date: 7/27/15
 * Time: 11:57 PM
 */

namespace App\Model;

use Nette\Object;

class OrderUserData extends Object
{

    /** @var  \Nette\Http\SessionSection */
    private $session;

    public function __construct(\Nette\Http\SessionSection $session)
    {
        $this->session = $session;
    }

    public function setValues(array $values)
    {
        foreach ($values as $k => $value) {
            $this->session->{$k} = $value;
        }
    }

    public function getValues()
    {
        return $this->session;
    }

    public function getValuesAsArray()
    {
        $arr = [];
        foreach ($this->session as $k => $item) {
            $arr[$k] = $item;
        }

        return $arr;
    }
}