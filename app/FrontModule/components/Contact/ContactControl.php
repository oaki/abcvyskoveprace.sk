<?php
namespace App\FrontModule\Components;

use App\Components\BaseControl;
use App\FrontModule\Forms\BaseForm;
use App\Services\Mail;
use Nette\Application\UI\Form;

class ContactControl extends BaseControl
{

    function render($id_node)
    {

        $template = $this->template;
        $template->setFile(dirname(__FILE__) . '/default.latte');
        $template->c = $this->getService('Contact')->fetch($id_node);

//		$param = array('id_node'=>$id_node);
        $template->id_node = $id_node;
        $this['form']['id_node']->setValue($id_node);
        echo $template;
    }

    function createComponentForm($name)
    {
        $form = new BaseForm($this, $name);
        $form->addText('name', 'Meno:')->addRule(Form::FILLED, 'Meno a priezvisko musia byť vyplnené.');
        $form->addText('surname', 'Priezvisko:')->addRule(Form::FILLED, 'Meno a priezvisko musia byť vyplnené.');
        $form->addText('company', 'Firma:');
        $form->addText('email', 'Email:');
        $form->addText('tel', 'Telefón:');
        $form->addTextarea('text', 'Textová správa:')->addRule(Form::FILLED, 'Správa musí byť vyplnená.');
        $form->addSubmit('btn_form', 'Odoslať správu');
        $form->addHidden('id_node');
        $form->onSuccess[] = [$this, 'handleSend'];

        return $form;
    }

    function handleSend(Form $form)
    {

        $values = $form->getValues();

        $contact_val = $this->getService('Contact')
            ->fetch($values['id_node']);

        if (!$contact_val) {
            $this->getPresenter()->flashMessage('Formulár sa nepodarilo odoslať. Skúste prosím neskôr.');
        }

        $template = $this->template;
        $template->setFile(dirname(__FILE__) . '/email.latte');
        $template->values = $values;

        /**
         * @var Mail $mail
         */
        $mail = $this->getService('Mail');

        $mail->setTemplateFile(dirname(__FILE__) . '/email.latte  ');
        if ($contact_val['email'] != '') {
            $mail->addTo($contact_val['email']);
        }

        $mail->setTemplate($template);
        $mail->setSubject($contact_val['email_subject']);
        $mail->send();

        $this->getPresenter()->flashMessage('Formulár bol úspešne odoslaný. Čoskoro Vás budeme kontaktovať.');
        $this->getPresenter()->redirect('this');

    }
}
