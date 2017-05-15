<?php
namespace App\AdminModule\CmsModule\Presenters;

use App\Components\Backend\File\FileControl;
use App\Components\Backend\Form\TwitterBootstrapForm;
use App\Model\Entity\SlideshowModel;
use Nette;
use Nette\Application;
use Nette\Application\UI\ITemplateFactory;
use Nette\DI\Container;
use Nette\Http;

/**
 * Description of Admin_Cms_SlideshowPresenter
 *
 * @author oaki
 */
class SlideshowPresenter extends BasePresenter
{

    /* @inject SlideshowModel */
    private $model;

//
//	function __construct(Container $context) {
//		parent::__construct($context);
//		$this->model = $this->getService('Slideshow');
//	}

    function handleAjaxSave()
    {
        $this->invalidateControl('flashmessage');
    }

    function renderDefault($id)
    {

        $value = $this->model->fetch($id);

        if (!$value AND $id != '')
            throw new Application\BadRequestException('Slideshow does not exist.');

        if ($value)
            $this['form']->setDefaults($value);
    }

    function handleSave(Application\UI\Form $form)
    {

        $v = $form->getValues();

        $this->model->update($v, $this->id);

        //musi sa zavolat change status node
        $this->getService('Node')->putInToTheSystem($this->id);

        $this->flashMessage('Prezentácia bola uložená');

        if ($this->isAjax()) {
            $this->invalidateControl('form');
            $this->invalidateControl('flashmessage');
        } else {
            $this->redirect('this');
        }

    }

    function createComponentForm()
    {

        $f                               = new TwitterBootstrapForm();
        $f->getElementPrototype()->class = 'ajax';

        $f->addText('title', 'Nadpis')
            ->setAttribute('placeholder', 'Sem vložte pomenovanie prezentácie')
            ->setAttribute('class', 'span9');

        $f->addSubmit('btn', 'Uložiť zmeny')
            ->getControlPrototype()->class = 'btn btn-success';

        $f->onSuccess[] = array($this, 'handleSave');

//		$f->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');

        return $f;
    }

    function createComponentFile($name)
    {

        $f = new FileControl($this, $name);

        $f->setIdFileNode($this->model->getIdFileNode($this->id));

//		$f->setTemplateName('slideshow');

        $f->addInput(array('type' => 'text', 'name' => 'name', 'css_class' => 'input-large name', 'placeholder' => 'Sem umiestnite názov'));
//		$f->addInput( array('type'=>'text','name'=>'link','css_class'=>'input-large link','placeholder'=>'Sem umiestnite link') );
//		$f->addInput( array('type'=>'text','name'=>'description','css_class'=>'input-large description','placeholder'=>'Sem umiestnite popis') );
//		

        $f->saveDefaultInputTemplate();

        return $f;
    }

    public function injectSlideshow(SlideshowModel $slideshowModel)
    {
        $this->model = $slideshowModel;
    }

}