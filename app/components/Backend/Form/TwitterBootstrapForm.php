<?php
namespace App\Components\Backend\Form;

use Nette\Application\UI\Form;

class TwitterBootstrapForm extends Form
{

    function __construct($parent = null, $name = null)
    {

        parent::__construct($parent, $name);

        $renderer->wrappers['controls']['container']     = null;
        $renderer->wrappers['pair']['container']         = 'div class=control-group';
        $renderer->wrappers['pair']['.error']            = 'error';
        $renderer->wrappers['control']['container']      = 'div class=controls';
        $renderer->wrappers['label']['container']        = 'div class=control-label';
        $renderer->wrappers['control']['description']    = 'span class=help-inline';
        $renderer->wrappers['control']['errorcontainer'] = 'span class=help-inline';

// make form and controls compatible with Twitter Bootstrap
        $this->getElementPrototype()->class('form-horizontal');

        foreach ($this->getControls() as $control) {
            $type = $control->getOption('type');
            if ($type === 'button') {
                $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn');
                $usedPrimary = true;

            } elseif (in_array($type, ['checkbox', 'radio'], true)) {
                $control->getLabelPrototype()->addClass($type);
                $control->getSeparatorPrototype()->setName(null);
            }
        }
//		$template = $this->getPresenter()->createTemplate();

//		$this->setRenderer(new \BootstrapRenderer($template));
    }
}