<?php

namespace App\AdminModule\EshopModule\Forms\ProductForm;

use App\Model\Entity\Eshop\ProductMarkModel;
use Nette\Application\UI\Form;

class ProductMarkForm extends Form
{

    /**
     * @var ProductMarkModel $markModel
     */
    private $markModel;

    function __construct($parent = null, $name = null, ProductMarkModel $markModel)
    {
        parent::__construct($parent, $name);
        $this->markModel = $markModel;
        $this->addFields();
        $this->getElementPrototype()->class = 'ajax';
    }

    function addFields()
    {
        $this->addText('name', 'Meno značky')->addRule(Form::FILLED, 'Názov musí byť vyplnený');
        $this->addSelect('top', 'Top', ['0' => 'nie', '1' => 'áno']);

        $this->addHidden('id_product_mark');
        $this->addSubmit('btn', 'Uložiť');
        $this->onSuccess[] = array($this, 'handleSave');
    }

    function handleSave()
    {

        $values = $this->getValues();

        $this->markModel->update($values, $values['id_product_mark']);

        $this->getPresenter()->flashMessage('Značka bola uložená.');
        $this->getPresenter()->redrawControl();
    }
}