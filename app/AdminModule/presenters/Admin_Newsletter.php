<?php

/**
 * Description of Admin_Newsletter
 *
 * @author oaki
 */
class Admin_NewsletterPresenter extends Admin_BasePresenter
{

    /** @persistent */
    public $id_newsletter_sended_msg = null;

    function beforeRender()
    {
        parent::beforeRender();

        $this['header']['css']->addFile('../jscripts/tabella/nette.tabella.css');
        $this['header']['js']->addFile('/tabella/nette.tabella.js');
        $this['header']->addJscriptFile('/jscripts/tiny_mce/tiny_mce.js');

        $this['header']['css']->addFile('admin/newsletter/newsletter.css');

    }

    function actionLoadMessage($id_newsletter_sended_msg)
    {
        $this->id_newsletter_sended_msg = $id_newsletter_sended_msg;
        $this->redirect('default');
    }

    /*
     * render
     */
    function renderDefault()
    {
        $this->template->form    = $this['newsletterTextForm'];
        $this->template->addform = $this['addForm'];

        $session = NEnvironment::getSession('checked_emails');
//		unset($session->emails);
        if (!isset($session->emails)) {
            $session->emails = array();
        }

        $this->template->checked_emails = $session->emails;

        $this->template->sended_msg = NewsletterModel::getSendedMsgFluent()->orderBy('date DESC')->fetchAll();

        if ($this->id_newsletter_sended_msg == null) {
            $newsletter = dibi::fetch("SELECT * FROM [newsletter_sended_msg] ORDER BY [date] DESC LIMIT 1");
        } else {
            $newsletter = dibi::fetch("SELECT * FROM [newsletter_sended_msg] WHERE id_newsletter_sended_msg = %i", $this->id_newsletter_sended_msg);
        }

        $this['newsletterTextForm']->setDefaults($newsletter);

    }

    /*
     * action
     */
    function handleDeleteEmail($id)
    {
        NewsletterModel::delete($id);
        $this['newsletterTabella']->invalidateControl();
    }

    function handleAddEmail(Form $form)
    {
        $values = $form->getValues();
        NewsletterModel::addEmails($values['emails']);

        $this->invalidateControl('newsletterTabella');
        $this->invalidateControl('addForm');
        $this->flashMessage('Email bol úspešne pridaný');
        $this->redirect('this');
    }

    function handleDeleteAllEmail()
    {

        NewsletterModel::deleteAllEmails();

        $session = NEnvironment::getSession('checked_emails');

        unset($session->emails);

        $this->invalidateControl('checkedEmails');

        $this['newsletterTabella']->invalidateControl();

    }

    function actionSendEmails(NButton $button)
    {

        $form   = $button->getForm();
        $values = $form->getValues();

        $session = NEnvironment::getSession('checked_emails');
        if (!empty($session->emails)) {
            $emails = $session->emails;
        } else {
            $emails = self::getFluent()->fetchAll();
        }

        $result = NewsletterModel::sendEmailToAll($emails, $values['subject'], $values['text']);

        if (!empty($result['errors'])) {
            foreach ($result['errors'] as $e) {
                $form->addError($e);
            }
        }

        NewsletterModel::saveMsg($values['subject'], $values['text']);

        if (!$form->hasErrors()) {
            $this->flashMessage('Newsletter bol úspešne odoslaný na všetky aktívne emaily');
            $this->redirect('this', array('id_newsletter_sended_msg' => null));
        }

    }

    function actionSendTestingEmail(NButton $button)
    {
        $form   = $button->getForm();
        $values = $form->getValues();
        if ($values['my_email'] == '') {
            $form->addError('Nebol zadaný email');

            return;
        }

        try {
            NewsletterModel::sendEmail($values['my_email'], $values['subject'], $values['text']);
            NewsletterModel::saveMsg($values['subject'], $values['text']);
            $this->flashMessage('Testovací email bol odoslaný na: ' . $values['my_email']);
            $this->redirect('this', array('id_newsletter_sended_msg' => null));
        } catch (Exception $e) {
            $form->addError($e->getMessage());
        }

    }

    function handleCheckedEmail($checked, array $id_newsletter_emails)
    {

        $session = NEnvironment::getSession('checked_emails');

        if ($checked) {
            foreach ($id_newsletter_emails as $id_newsletter_email)
                $session->emails[$id_newsletter_email] = dibi::fetch("SELECT * FROM [newsletter_emails] WHERE id_newsletter_emails = %i", $id_newsletter_email);
        } else {
            foreach ($id_newsletter_emails as $id_newsletter_email)
                unset($session->emails[$id_newsletter_email]);
        }

        $this->invalidateControl('checkedEmails');

    }

    function handleUncheckedAllEmail()
    {

        $session = NEnvironment::getSession('checked_emails');

        unset($session->emails);

        $this->invalidateControl('checkedEmails');

    }

    function createComponent($name)
    {
        switch ($name) {
            /*
             * newsletterTabella
             */
            case 'newsletterTabella':

                $grid = new Tabella(NewsletterModel::getFluent()->toDataSource(),
                    array(
                        'sorting'   => 'desc',
                        'order'     => 'adddate',
                        'id_table'  => 'id',
                        'limit'     => 100,
                        'onSuccess' => function ($values) {

                            NewsletterModel::edit($values, $values['id_newsletter_emails']);
                        },
                        'onDelete'  => function ($id_newsletter_emails) {
                            NewsletterModel::delete($id_newsletter_emails);
                        }
                    )
                );

                $el = NHtml::el("div");

                $session = NEnvironment::getSession('checked_emails');

                $grid->addColumn($el, "", array("editable" => false,
                                                'filter'   => false,
                                                "width"    => 20,
                                                "renderer" => function ($row) use ($session) {
                                                    $el = NHtml::el("td");

                                                    $checked = (isset($session->emails[$row['id_newsletter_emails']])) ? 'checked="checked"' : '';
                                                    $el->add(
                                                        '<input class="checked_emails" type="checkbox" name="se[]" value="' . $row['id_newsletter_emails'] . '" ' . $checked . '/>'
                                                    );

                                                    return $el;
                                                }
                ));

                $grid->addColumn("Id", "id_newsletter_emails", array("width" => 30, 'editable' => false));
                $grid->addColumn("Email", "email", array("width" => 50, 'editable' => false));
//				$grid->addColumn( "Popis", "description", array( 'editable'=>true ) );
                $grid->addColumn("Dátum registrácie ", "adddate", array("width" => 100));

                /*$grid->addColumn( "Aktívny", "active",
                        array(
                            "width" => 50,
                            'type'=>  Tabella::SELECT,
                            "editable" => true,
                            "filter" => array( 1=>'áno', 0=>'nie'),
                            'renderer' => function($row){
                                $active = ($row['active'] == 1)?'áno':'nie';
                                $el = NHtml::el( "td" )->setHtml($active);
                                return $el;
                            }
                        )
                );

*/
                $grid->addColumn("Zmazať", "Zmazať",
                    array(
                        "width"   => 30,
                        'filter'  => null,
                        "options" => '',

                        "renderer" => function ($row) {
                            $el = NHtml::el("td");

                            $el->add(
                                NHtml::el('a')->href(
                                    NEnvironment::getApplication()->getPresenter()->link('deleteEmail!', array('id' => $row->id_newsletter_emails))
                                )->addClass('deleteIcon ajax')
                            );

                            $span = NHtml::el('span');

                            $el->add($span);

                            return $el;
                        }
                    )
                );

                $this->addComponent($grid, $name);
                break;

            /*
             * newslette text
             */
            case 'newsletterTextForm':

//			 $last_newsletter = dibi::fetch("SELECT * FROM [newsletter_sended_msg] ORDER BY [date] DESC LIMIT 1");

                $f = new Form($this, $name);

                $f->addText('subject', 'Predmet')->addRule(Form::FILLED, 'Predmet musí byť vyplnený.');
                $f->addTextArea('text', '');
                $f->addText('my_email', 'Testovací email');
                $f->addSubmit('btn_send_emails', 'Odoslať všetkým')->onClick[]        = array($this, 'actionSendEmails');
                $f->addSubmit('btn_send_to_me', 'Odoslať testovací email')->onClick[] = array($this, 'actionSendTestingEmail');

//			 if(!$last_newsletter){
//				 $email_template = new NFileTemplate;
//				 $email_template->registerFilter(new NLatteFilter);
//
//				 $email_template->setFile(WWW_DIR.'/newsletter/templates/1/1.phtml');
//				 
//				 
//				 $email_template->text = '';
//				 $values['text'] = (string)$email_template;
//			}else{
//				$values = $last_newsletter;
//			}

//			 $f->setDefaults($values);
                return $f;
                break;

            case 'addForm':
                $f = new Form();
                $f->addText('emails', 'Email');

                $f->addSubmit('btn', 'Pridať');
                $f->onSuccess[] = array($this, 'handleAddEmail');

                return $f;

                break;
            default:
                return parent::createComponent($name);
                break;
        }
    }//end createComponent

}