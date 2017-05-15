<?php

namespace App\FrontModule\Forms;

use App\Model\Entity\UserModel;
use Nette\Application\UI\Form;
use Nette\Object;

class RegistrationFormFactory extends Object
{

    /**
     * @return Form
     */
    public function create($presenter, $name)
    {
        $form = new BaseForm($presenter, $name);

        $iso = ['SVK' => 'Slovensko'];

        $form->addText('login', 'Používateľské meno / Email')
            ->addRule(Form::FILLED, 'Používateľské meno musí byť vyplnené')
            ->addRule(Form::EMAIL, 'Používateľské meno musí byť email.');

        $form->addPassword('password', 'Heslo');

        $form->addPassword('passwordCheck', 'Znova heslo')
            ->addConditionOn($form['password'], Form::FILLED)
            ->addRule(Form::FILLED, 'Zadejte heslo pro kontrolu')
            ->addRule(Form::EQUAL, 'Hesla sa musia zhodovať', $form['password']);

        /*
         * Info o uzivatelovi
         */

        $form->addRadioList('type', '', [0 => 'Súkromná osoba', 1 => 'Podnikateľ - firma'])
            ->addRule(Form::FILLED, 'Uveďte či ste súkromná osoba alebo firma')
            ->setDefaultValue(0);

        $form->addText('name', 'Meno')
            ->addRule(Form::FILLED, 'Meno musí byť vyplnené');

        $form->addText('surname', 'Priezvisko')
            ->addRule(Form::FILLED, 'Priezvisko musí byť vyplnené');

        $form->addText('address', 'Adresa')
            ->addRule(Form::FILLED, 'Adresa musí byť vyplnená');

        $form->addText('city', 'Mesto')
            ->addRule(Form::FILLED, 'Mesto musí byť vyplnené');

        $form->addText('zip', 'PSČ')
            ->addRule(Form::FILLED, 'PSČ musí byť vyplnené');

        $form->addSelect('iso', 'Štát', $iso)
            ->addRule(Form::FILLED, 'Štát musí byť vyplnené');

        $form->addText('phone', 'Telefón')
            ->addRule(Form::FILLED, 'Telefón musí byť vyplnený');

        $form->addText('company_name', 'Názov spoločnosti ')
            ->addConditionOn($form['type'], Form::EQUAL, 1)
            ->addRule(Form::FILLED, 'Názov spoločnosti musí byť vyplnený');

        $form->addText('ico', 'IČO')
            ->addConditionOn($form['type'], Form::EQUAL, 1)
            ->addRule(Form::FILLED, 'IČO spoločnosti musí byť vyplnené')
            ->addRule(Form::MAX_LENGTH, ('Maximálna dĺžka je 12'), 12);

        $form->addRadioList('paying_vat', 'Platca DPH', array(0 => 'platca', 1 => 'neplatca'))
            ->setDefaultValue(0)
            ->addConditionOn($form['type'], Form::EQUAL, 1)
            ->addRule(Form::FILLED, 'IČO spoločnosti musí byť vyplnené');

        $form->addText('dic', 'DIČ')
            ->addConditionOn($form['type'], Form::EQUAL, 1)
            ->addRule(Form::FILLED, 'DIČ spoločnosti musí byť vyplnené');

        $form->addRadioList('use_delivery_address', 'Dodacia adresa', [
                0 => 'Prednastavená (rovnaká ako fakturačná adresa)',
                1 => 'Iná']
        )->setDefaultValue(0);

        $form->addText('delivery_name', 'Meno')
            ->addConditionOn($form['use_delivery_address'], Form::EQUAL, 1)
            ->addRule(Form::FILLED, 'Meno musí byť vyplnené');

        $form->addText('delivery_surname', 'Priezvisko')
            ->addConditionOn($form['use_delivery_address'], Form::EQUAL, 1)
            ->addRule(Form::FILLED, 'Priezvisko musí byť vyplnené');

        $form->addText('delivery_address', 'Adresa')
            ->addConditionOn($form['use_delivery_address'], Form::EQUAL, 1)
            ->addRule(Form::FILLED, 'Adresa musí byť vyplnená');

        $form->addText('delivery_city', 'Mesto')
            ->addConditionOn($form['use_delivery_address'], Form::EQUAL, 1)
            ->addRule(Form::FILLED, 'Mesto musí byť vyplnené');

        $form->addText('delivery_zip', 'PSČ')
            ->addConditionOn($form['use_delivery_address'], Form::EQUAL, 1)
            ->addRule(Form::FILLED, 'PSČ musí byť vyplnené');

        $form->addSelect('delivery_iso', 'Štát', $iso)
            ->addConditionOn($form['use_delivery_address'], Form::EQUAL, 1)
            ->addRule(Form::FILLED, 'Štát musí byť vyplnené');

        $form->addText('delivery_phone', 'Telefón')
            ->addConditionOn($form['use_delivery_address'], Form::EQUAL, 1)
            ->addRule(Form::FILLED, 'Štát musí byť vyplnené');

//        $form->addTextArea('notice', 'Poznámka');

        $form->addCheckbox('agree', 'Súhlasím s obchodné podmienky')
            ->addRule(Form::FILLED, 'Súhlas je povinný');

        $form->addCheckbox('delivery_terms', 'Súhlasím s doručením do 10 dní')
            ->addRule(Form::FILLED, 'Súhlas je povinný');

        return $form;
    }

    public function processForm(Form $f)
    {
        $values = $f->getValues();

        dump($values);
        exit;
//        $this->userModel->connection->begin();

    }

}
