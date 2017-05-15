<?php
namespace App\FrontModule\Components;

use App\Components\BaseControl;
use App\FrontModule\Forms\BaseForm;
use Nette\Application\UI\Form;
use     App\FrontModule\components\OrderSummaryControl\OrderSummary;

class UploadLogoControl extends BaseControl
{

    /**
     * @var OrderSummary
     */
    private $orderSummary;

    /**
     * @return OrderSummary
     */
    public function getOrderSummary()
    {
        return $this->orderSummary;
    }

    /**
     * @param OrderSummary $orderSummary
     */
    public function setOrderSummary($orderSummary)
    {
        $this->orderSummary = $orderSummary;
    }

    function render()
    {
        $template       = $this->template;
        $template->logo = $this->orderSummary['logo'];
        $template->setFile(dirname(__FILE__) . '/default.latte');
        $template->render();
    }

    protected function createComponentUploadLogoForm($name)
    {
        $f = new BaseForm($this, $name);
        $f->addUpload('FILE', 'Obrázok')
            ->addCondition(Form::FILLED)
//            ->addRule(Form::MIME_TYPE, 'Súbor musí byť JPEG, PNG alebo GIF.')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximálna velikosť súboru je 2MB.', 2 * 1024 * 1024 /* v bytech */);

        $f->addSubmit('btn', 'Nahrať');

        $f->onSuccess[] = $this->processForm;

        return $f;
    }

    public function handleDeleteImage()
    {
        $this->orderSummary['logo'] = '';
        $this->getPresenter()->flashMessage('Vaše logo bolo zmazané.', 'alert-success');
        $this->getPresenter()->redirect('this');
    }

    public function processForm(Form $form)
    {
        $values = $form->getValues();
        if ($values['FILE']->isOK()) {

            $filename = $values['FILE']->getSanitizedName();
            $info     = pathinfo($filename);
            $filename = 'printLogo_' . $info['filename'] . '-' . time() . '.' . $info['extension'];

            $values['FILE']->move($this->getPresenter()->context->parameters['printLogUploadDir'] . "/" . $filename);

            $values['FILENAME'] = $filename;

            $this->orderSummary['logo'] = $filename;
            $this->getPresenter()->flashMessage('Vaše logo bolo úspešne nahraté', 'alert-success');
            $this->getPresenter()->redirect('this');
        } else {
            $form->addError("Upload file was not successful");
        }
    }

}