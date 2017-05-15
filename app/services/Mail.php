<?php
/**
 * Created by PhpStorm.
 * User: pavolbincik
 * Date: 8/1/16
 * Time: 6:05 PM
 */

namespace App\Services;

use Nette\Application\UI\ITemplateFactory;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\Localization\ITranslator;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

class Mail
{

    /**
     * @var array
     */
    private $configuration;

    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * @var ITemplateFactory
     */
    private $templateFactory;

    /**
     * @var ILatteFactory
     */
    private $template;

    private $from, $to, $bcc, $subject, $templateFile;

    /**
     * @var Message
     */
    private $mail;

    /**
     * Mail constructor.
     *
     * @param array            $configuration
     * @param ITemplateFactory $templateFactory
     * @param ITranslator      $translator
     */
    public function __construct(array $configuration, ITemplateFactory $templateFactory, ITranslator $translator)
    {
        $this->configuration   = $configuration;
        $this->translator      = $translator;
        $this->templateFactory = $templateFactory;

        $this->setFrom($configuration['from']);
        $this->setTo($configuration['to']);
        $this->setBcc($configuration['bcc']);

        $this->template = $this->templateFactory->createTemplate();

        $this->mail = new Message();
    }

    private function loadDefaults()
    {
        $this->setFrom($this->configuration['from']);
        $this->setTo($this->configuration['to']);
        $this->setBcc($this->configuration['bcc']);

        $this->setSubject(null);
        $this->setTemplateFile(null);
        $this->mail = new Message();
    }

    public function send()
    {
        $this->template->setFile($this->getTemplateFile());

        $this->mail->setFrom($this->getFrom())
            ->addTo($this->getTo())
            ->addBcc($this->getBcc())
            ->setSubject($this->translator->translate($this->getSubject()))
            ->setHtmlBody($this->template, $this->template->baseUrl);

        $mailer = new SendmailMailer();
        $mailer->send($this->mail);

        $this->loadDefaults();
    }

    public function addAttachment($name, $content = null, $contentType = null)
    {
        $this->mail->addAttachment($name, $content, $contentType);

        return $this;
    }

    public function setTemplateValues($name, $values)
    {
        $this->template->$name = $values;

        return $this;
    }

    public function show()
    {
        $this->template->setFile($this->getTemplateFile());

        $this->mail->setFrom($this->getFrom())
            ->addTo($this->getTo())
            ->addBcc($this->getBcc())
            ->setSubject($this->translator->translate($this->getSubject()))
            ->setHtmlBody($this->template, $this->template->baseUrl);
        echo $this->mail->getHtmlBody();
    }

    /**
     * @return mixed
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param $from
     *
     * @return $this
     */
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param $to
     *
     * @return $this
     */
    public function setTo($to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * @param $bcc
     *
     * @return $this
     */
    public function setBcc($bcc)
    {
        $this->bcc = $bcc;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param $subject
     *
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTemplateFile()
    {
        return $this->templateFile;
    }

    /**
     * @param $templateFile
     *
     * @return $this
     */
    public function setTemplateFile($templateFile)
    {
        $this->templateFile = $templateFile;

        return $this;
    }

    /**
     * @return ILatteFactory
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param $template
     *
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

}