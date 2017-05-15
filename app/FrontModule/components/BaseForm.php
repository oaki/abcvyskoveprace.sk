<?php
namespace App\FrontModule\Forms;

use Nette;
use Nette\Application\UI\Form;

class BaseForm extends Form
{

    public function __construct(Nette\ComponentModel\IContainer $parent = null, $name = null)
    {
        parent::__construct($parent, $name);
    }

}