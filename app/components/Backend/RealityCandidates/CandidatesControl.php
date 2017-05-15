<?php

class CandidateControl extends BaseControl
{

    private $id_reality;

    private $model;

    /** @persistent */
    public $id;

    function __construct(\IComponentContainer $parent = null, $name = null)
    {
        parent::__construct($parent, $name);

        $this->model = $this->getService('RealityCandidate');

    }

    function handleEdit($id)
    {
        $this->id = $id;
        $this['form']->setDefaults($this->model->fetch($id));
        $this->invalidateControl();
    }

    function handleDelete($id)
    {
        $this->model->delete($id);

        $this->invalidateControl();
    }

    public function render()
    {
        $this->template->time      = time();
        $template                  = $this->template;
        $template->candidates_list = $this->model->getFluent()->where('id_reality = %i', $this->id_reality);
        $template->setFile(dirname(__FILE__) . '/default.latte');
        $template->render();

        if ($this->id) {
            $template->candidate = $this->model->fetch($this->id);
        }

    }


//	public function render($id_reality_candidate){
//		
//		$template = $this->template;
//		$template->article = $this->getService('Candidate')->get($id_reality_candidate);
//		$template->setFile(dirname(__FILE__) . '/default.latte');
//		$template->render();
//	}

    function setIdReality($id_reality)
    {
        $this->id_reality = $id_reality;
    }

    function handleAdd()
    {
        $this->invalidateControl();
    }

    function handleSave(NForm $form)
    {
        $values = $form->getValues();

        if (!isset($values['id_reality'])) {
            $values['id_reality'] = $this->getPresenter()->getParam('id');
        }
//		dde($values);
        if (!isset($values['id_reality_candidate']) OR $values['id_reality_candidate'] == '')
            $this->model->insert($values);
        else
            $this->model->update($values, $values['id_reality_candidate']);

        $this->invalidateControl();
    }

    function createComponentForm($name)
    {
        $f = new CandidateForm;
        $f->addSubmit('btn', 'Uložiť')->setAttribute('class', 'btn btn-primary');
        $f->addHidden('id_reality_candidate');
        $f->onSuccess[] = array($this, 'handleSave');

        return $f;
    }
}