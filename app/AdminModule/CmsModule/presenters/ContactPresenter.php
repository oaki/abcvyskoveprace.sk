<?php
namespace App\AdminModule\CmsModule\Presenters;

use App\Model\Entity\ContactModel;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Utils\Html;

/**
 * ContactPresenter presenter.
 *
 * @author     Pavol Bincik
 * @package    CMS
 */
class ContactPresenter extends BasePresenter
{

    private $model;

    function actionAdd()
    {
        $this->setView('default');
    }

    function renderDefault($id)
    {

        $f = $this->createComponentForm();

        $value = $this->model->fetch($id);

        if (!$value AND $id != '')
            throw new BadRequestException('Contact does not exist.');

        $this['form']->setDefaults(array('latitude' => '49.403628', 'longitude' => '19.476898800000072'));

        if ($value)
            $this['form']->setDefaults($value);;
    }

    function handleSave(Form $form)
    {

        $v = $form->getValues();

        $this->model->update($v, $this->id);

        //musi sa zavolat change status node
        $this->getService('Node')->putInToTheSystem($this->id);

        $this->flashMessage('Contact bola uložená');

        if ($this->isAjax()) {
            $this->invalidateControl('form');
            $this->invalidateControl('flashmessage');
        } else {
            $this->redirect('this');
        }

    }

    function createComponentForm()
    {

        $f                               = new Form;
        $f->getElementPrototype()->class = 'ajax';

        $f->addGroup('Obsah');
        $f->addText('title', 'Nadpis')
            ->setAttribute('placeholder', 'Sem vložte nadpis')
            ->setAttribute('class', 'span9');

        $f->addTextarea('text', 'Text')
            ->setAttribute('class', 'span9 mceEditor');

        $f->addGroup('Nastavenie Google Maps');

        $f->addText('address', 'Adresa')
            ->setAttribute('placeholder', 'Adresa')
            ->setAttribute('class', 'span9 address');

        $f->addText('latitude', 'Latitude')
            ->setAttribute('class', 'input-large latitude');

        $f->addText('longitude', 'Longitude')
            ->setAttribute('class', 'input-large longitude');

        $f->addTextArea('google_text', 'Text do google map')
            ->setAttribute('class', 'mceEditor')
            ->setOption('description', Html::el('span id=map_canvas'));

        $f->addGroup('Nastavenie formuláru');

        $f->addText('email_subject', 'Predmety odosielanej správy')
            ->setAttribute('class', 'span9');

        $f->addText('email', 'Poslať na email na')
            ->setAttribute('class', 'span9');

        $f->addGroup('Btn');
        $f->addSubmit('btn', 'Uložiť zmeny')
            ->getControlPrototype()->class = 'btn btn-success';

        $f->onSuccess[] = array($this, 'handleSave');

        $f->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');

        return $f;
    }

    function createComponentFile($name)
    {

        $f = new FileControl($this, $name);

        $f->setIdFileNode($this->model->getIdFileNode($this->id));

        $f->setTemplateName('slideshow');

        $f->addInput(array('type' => 'text', 'name' => 'name', 'css_class' => 'input-large name', 'placeholder' => 'Sem umiestnite názov'));

        $f->saveDefaultInputTemplate();

        return $f;
    }

    public function injectContactModel(ContactModel $model)
    {
        $this->model = $model;
    }
}