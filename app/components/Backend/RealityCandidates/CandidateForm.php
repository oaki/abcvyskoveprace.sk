<?php

class CandidateForm extends TwitterBootstrapForm
{

    function __construct(IComponentContainer $parent = null, $name = null)
    {
        parent::__construct($parent, $name);
        $this->addFields();
    }

    function addFields()
    {
        $this->getForm()->getElementPrototype()->class = 'ajax';
        $this->addText('name', 'Meno')
            ->addRule(NForm::FILLED, 'Meno musí byť vyplnené');
        $this->addText('surname', 'Priezvisko')
            ->addRule(NForm::FILLED, 'Priezvisko musí byť vyplnené');
        $this->addText('address', 'Adresa');
        $this->addTextarea('text', 'Text');
    }

}