<?php
namespace App\AdminModule\UserModule\Forms;

use App\Components\Backend\Form\TwitterBootstrapForm;
use App\Model\Entity\UserModel;
use Nette\Application\UI\Form;
use Nette\ComponentModel\Container;

class UserBaseForm extends TwitterBootstrapForm
{

    private $userModel;

    function __construct(UserModel $userModel, Container $parent = null, $name = null)
    {
        parent::__construct($parent, $name);

        $this->userModel = $userModel;

        $this->addFields();
    }

    function addFields()
    {
        $form = $this;

        $form->addGroup(('Prihlasovacie údaje'));
        $form->addText('login', ('Email'))
            ->addRule(Form::FILLED, ('Používateľské meno musí byť vyplnené'))
            ->addRule(Form::EMAIL, ('Používateľské meno musí byť email.'));

        $form->addPassword('password', ('Heslo'));

        $form->addPassword('passwordCheck', ('Znova heslo'))
            ->addConditionOn($form['password'], Form::FILLED)
            ->addRule(Form::FILLED, ('Zadejte heslo pro kontrolu'))
            ->addRule(Form::EQUAL, ('Hesla sa musia zhodovať'), $form['password']);

        /*
         * Info o uzivatelovi
         */
        $form->addGroup('Informácie');
        $form->addRadioList('type', '', array(0 => ('Fyzická osoba'), 1 => ('Právnicka osoba')))
            ->addRule(Form::FILLED, ('Uveďte či ste Realitná kancelária alebo Developer'))
            ->setDefaultValue(0);

        //$form->addSelect('title', ('Oslovenie'), array( 0=>('Žiadne'), 1=>('Pán'),2=>('Pani'),3=>('Slečna') ));
        $form->addText('name', ('Meno'))
            ->addRule(Form::FILLED, ('Meno musí byť vyplnené'));

        $form->addText('surname', ('Priezvisko'))
            ->addRule(Form::FILLED, ('Priezvisko musí byť vyplnené'));

        $form->addText('address', ('Adresa'))
            ->addRule(Form::FILLED, ('Adresa musí byť vyplnená'));

        $form->addText('city', ('Mesto'))
            ->addRule(Form::FILLED, ('Mesto musí byť vyplnené'));

        $form->addText('zip', ('PSČ'))
            ->addRule(Form::FILLED, ('PSČ musí byť vyplnené'));

        $form->addSelect('iso', ('Štát'), $this->userModel->getAllCountry())
            ->addRule(Form::FILLED, ('Štát musí byť vyplnený'));

        $form->addText('phone', ('Telefón'))
            ->addRule(Form::FILLED, ('Telefón musí byť vyplnený'));

        //$form->addText('fax', ('Fax'));

        $form->addGroup(('Firemné informácie'));

        $form->addText('company_name', ('Názov spoločnosti '))
            ->addConditionOn($form['type'], self::EQUAL, 1)
            ->addRule(Form::FILLED, ('Názov spoločnosti musí byť vyplnený'));

        $form->addText('ico', ('IČO'))
            ->addConditionOn($form['type'], self::EQUAL, 1)
            ->addRule(Form::FILLED, ('IČO spoločnosti musí byť vyplnené'))
            ->addRule(Form::MAX_LENGTH, ('Maximálna dĺžka je 12'), 12);

        $form->addRadioList('paying_vat', ('Platca DPH'), array(0 => 'platca', 1 => 'neplatca'))
            ->setDefaultValue(0)
            ->addConditionOn($form['type'], self::EQUAL, 1)
            ->addRule(Form::FILLED, ('IČO spoločnosti musí byť vyplnené'));

        $form->addText('dic', ('DIČ'))
            ->addConditionOn($form['type'], self::EQUAL, 1)
            ->addRule(Form::FILLED, ('DIČ spoločnosti musí byť vyplnené'));

        $form->addGroup('');

        $form->addRadioList('use_delivery_address', ('Dodacia adresa'), array(0 => ('Prednastavená (rovnaká ako fakturačná adresa)'), 1 => ('Iná')))
            ->setDefaultValue(0);

        $form->addGroup(('Dodacia adresa'));

        //$form->addSelect('title', ('Oslovenie'), array( 0=>('Žiadne'), 1=>('Pán'),2=>('Pani'),3=>('Slečna') ));
        $form->addText('delivery_name', ('Meno'))
            ->addConditionOn($form['use_delivery_address'], self::EQUAL, 1)
            ->addRule(Form::FILLED, ('Meno musí byť vyplnené'));

        $form->addText('delivery_surname', ('Priezvisko'))
            ->addConditionOn($form['use_delivery_address'], self::EQUAL, 1)
            ->addRule(Form::FILLED, ('Priezvisko musí byť vyplnené'));

        $form->addText('delivery_address', ('Adresa'))
            ->addConditionOn($form['use_delivery_address'], self::EQUAL, 1)
            ->addRule(Form::FILLED, ('Adresa musí byť vyplnená'));

        $form->addText('delivery_city', ('Mesto'))
            ->addConditionOn($form['use_delivery_address'], self::EQUAL, 1)
            ->addRule(Form::FILLED, ('Mesto musí byť vyplnené'));

        $form->addText('delivery_zip', ('PSČ'))
            ->addConditionOn($form['use_delivery_address'], self::EQUAL, 1)
            ->addRule(Form::FILLED, ('Priezvisko musí byť vyplnené'));

        $form->addSelect('delivery_iso', ('Štát'), $this->userModel->getAllCountry())
            ->addConditionOn($form['use_delivery_address'], self::EQUAL, 1)
            ->addRule(Form::FILLED, ('Priezvisko musí byť vyplnené'));

        $form->addText('delivery_phone', ('Telefón'));
    }
}