<?php
namespace App\FrontModule\Presenters;

use App\FrontModule\Forms\BaseForm;
use App\FrontModule\Forms\RegistrationFormFactory;
use App\Model\Entity\UserModel;
use App\Services\Mail;
use Nette\Application\UI\Form;
use Nette\InvalidStateException;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

class RegistrationPresenter extends BasePresenter
{

    public $userModel;

    /**
     * @var Mail @inject
     */
    public $mailer;

    /**
     * @var RegistrationFormFactory @inject
     */
    public $registrationFormFactory;

    public function beforeRender()
    {
        if ($this->user->isLoggedIn()) {
            $this->redirect('Profile:default');
        }

        parent::beforeRender();

    }

    public function renderDefault()
    {
    }

    public function renderRenewPassword($confirm_hash, $email)
    {
        $this['renewPasswordForm']->setDefaults([
            'confirm_hash' => $confirm_hash, 'email' => $email
        ]);
    }

    protected function createComponentRegistrationForm($name)
    {
        $f = $this->registrationFormFactory->create($this, $name);

        $f['password']->addRule(Form::FILLED, 'Heslo musí byť vyplnené')
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mať aspoň %d znakov.', 6);

        $f->addSubmit('submit', 'Registrovať');

        $f->onSuccess[] = $this->processRegistrationForm;

        return $f;
    }

    public function processRegistrationForm(Form $form)
    {
        $values = $form->getValues(true);
        $user   = $this->userModel->getFluent('1')->where('login = %s', $values['login'])->fetchSingle();

        try {
            if ($user)
                throw new InvalidStateException('Uživateľ pod týmto prihlasovacím meno už existuje.');

            unset($values['passwordCheck']);

            $values['activate'] = 1;

            $this->flashMessage("Registrácia je dokončená, účet aktivovaný a ste prihlásený na stránke.");

            $template = clone $this->template;

            $template->footer = $this->getService('SettingModel')->getValueByName('footer_for_emails');
            $template->values = $values;
            $this->mailer
                ->setSubject('Informácie o účte')
                ->setTo($values['login'])
                ->setBcc($this->context->parameters['mailer']['to'])
                ->setTemplate($template)
                ->setTemplateFile(dirname(__FILE__) . '/templates/Registration/registrationConfirmEmail.latte')
                ->send();

            $this->userModel->insert($values);

            $this->user->login($values ['login'], $values ['password']);

            if ($this->backlink != '') {
                $this->restoreRequest($this->backlink);
            } else {
                $this->redirect('Homepage:default');
            }
        } catch (InvalidStateException $e) {
            $form->addError($e->getMessage());
        }
    }

    protected function createComponentRenewPasswordForm($name)
    {
        $f = new BaseForm($this, $name);
        $f->addProtection('Sedenie vypršalo. Prosím obnovte formulár a zadajte údaje znovu.');
        $f->addPassword('password', 'Heslo', 30)
            ->addRule(Form::MIN_LENGTH, 'Heslo musí mať aspoň %d znakov.', 6)
            ->addRule(Form::FILLED, 'Heslo musí byť vyplnené.');

        // Overenie hesla
        $f->addPassword('confirmPassword', 'Potvrdenie hesla', 30)
            ->addRule(Form::FILLED, 'Potvrdenie hesla musí byť vyplnené.')
            ->addRule(Form::EQUAL, 'Zadané heslá sa musia zhodovať.', $f['password']);

        $f->addSubmit('btn', 'Zmeniť heslo');
        $f->addHidden('confirm_hash');
        $f->addHidden('email');

        $f->onSuccess[] = $this->processRenewPassword;

        return $f;
    }

    public function processRenewPassword(Form $form)
    {
        $values = $form->getValues();

        if ($values['confirm_hash'] != ''
            && $user_id = $this->userModel->getFluent('ID')
                ->where('EMAIL = %s', $values['email'], 'AND CONFIRM_HASH = %s', $values['confirm_hash'])
                ->fetchSingle()
        ) {
            $this->userModel->update([
                'PASSWORD'     => UserModel::getHash($values['password']),
                'CONFIRM_HASH' => null
            ], $user_id);

            $this->flashMessage('Vaše heslo bolo úspešne zmenené', 'alert-success');
            $this->redirect('this');
        } else {
            $form->addError('Overovací kľuč je neplatný. Vygenerujte si prosím nový v sekcii "Zabudol som heslo"');
        }
    }

    protected function createComponentLostPasswordForm($name)
    {
        $f = new BaseForm($this, $name);
        $f->addProtection('Sedenie vypršalo. Prosím obnovte formulár a zadajte údaje znovu.');
        $f->addText('email', 'E-mail')
            ->addRule(Form::FILLED, 'Email musí byť vyplnený.')
            ->addRule(Form::EMAIL, 'Email nie je v správnom tvare');

        $f->addSubmit('btn', 'Zmeniť heslo');

        $f->onSuccess[] = $this->processLostPassword;

        return $f;
    }

    public static function random($length, $base = "abcdefghjkmnpqrstwxyz123456789")
    {
        $max    = strlen($base) - 1;
        $string = "";

        mt_srand((double)microtime() * 1000000);
        while (strlen($string) < $length)
            $string .= $base[mt_rand(0, $max)];

        return $string;
    }

    public function processLostPassword(Form $form)
    {
        $values = $form->getValues();

        $user = $this->userModel->getFluent('id_user, login')->where("login = %s", $values['email'], 'AND activate = 1')->fetch();
        if ($user) {
            $template = $this->template;

            $new_password = self::random(8);

            $this->userModel->update(array('new_password' => UserModel::getHash($new_password)), $user['id_user']);

            $template->new_password = $new_password;

            $template = clone $this->template;

            $template->footer = $this->getService('SettingModel')->getValueByName('footer_for_emails');

            $this->mailer
                ->setSubject('Stratené heslo')
                ->setTo($values['email'])
//                ->setBcc( $this->context->parameters['mailer']['to'])
                ->setTemplate($template)
                ->setTemplateFile(dirname(__FILE__) . '/templates/Registration/email_lost_pass.latte')
                ->send();
        }

        $this->flashMessage('Vaše nové heslo vám bude v krátkej dobe zaslané na "' . $values['email'] . '".');
        $this->redirect('this');
    }

    public function injectUserModel(UserModel $service)
    {
        $this->userModel = $service;
    }
}