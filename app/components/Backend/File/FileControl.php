<?php

namespace App\Components\Backend\File;

use App\Components\BaseControl;

class FileControl extends BaseControl
{

    public $id_file_node;

    public $template_name = 'default';

    /*
     * use for each image,
     * you can add to images more param like title, link, text....
     * array
     */
    protected $inputs = array();

    function render()
    {

        $template = $this->template;
        $this->template->setFile(dirname(__FILE__) . '/default.latte');

        $template->render();
    }

    function setIdFileNode($id_file_node)
    {
        $this->id_file_node = (int)$id_file_node;
    }

    function setTemplateName($template_name)
    {
        $this->template_name = $template_name;
    }

    /*
     * @return $inputs
     */

    function getInputs()
    {
        return $this->inputs;
    }

    function addInputs(array $inputs)
    {
        foreach ($inputs as $input) {
            $this->addInput($input);
        }
    }

    private function serializeInput()
    {
        return serialize($this->inputs);
    }

    function saveDefaultInputTemplate()
    {
        if ($this->id_file_node == null)
            throw new Exception('id_file_node is NOT set');

        $model = $this->getService('FileNode');

        $p = $model->fetch($this->id_file_node);

        $inputs_serialized = $this->serializeInput();
//		dde($inputs_serialized);
        if ($p['default_file_param'] != $inputs_serialized) {
            $model->update(array('default_file_param' => $inputs_serialized), $this->id_file_node);
        }
    }

    function addInput(array $input)
    {
        $this->inputs[] = $input;
    }

}