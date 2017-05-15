<?php
/**
 * Created by PhpStorm.
 * User: pavolbincik
 * Date: 7/23/15
 * Time: 9:46 PM
 */

namespace App\FrontModule\components\OrderSummaryControl;

use App\Components\BaseControl;
use App\FrontModule\Forms\BaseForm;
use Nette\Application\UI\Form;

class OrderSummaryControl extends BaseControl
{

    /**
     * @var OrderSummary
     */
    private $model;

    /**
     * @var string
     */
    private $templateFile;

    /**
     * OrderSummaryControl constructor.
     *
     * @param OrderSummary $model
     */
    public function __construct(OrderSummary $model)
    {
        $this->model        = $model;
        $this->templateFile = __DIR__ . '/default.latte';

    }

    public function render()
    {
        $this->template->setFile($this->templateFile);
        $this->template->cart  = $this->model->getCart();
        $this->template->model = $this->model;
        $this['couponForm']->setDefaults([
            'coupon' => $this->model['coupon']
        ]);

        $this->template->render();
    }

    /**
     * Component factory. Delegates the creation of components to a createComponent<Name> method.
     *
     * @param  string      component name
     *
     * @return IComponent  the created component (optionally)
     */
    protected function createComponentCouponForm($name)
    {
//        $this->getContext()->getService('translator')
        $f = new BaseForm($this, $name);
        $f->addText('coupon', 'Kupón')
            ->setAttribute('placeholder', $this->getContext()->getService('translator')->translate('Kupón'))
            ->addRule(Form::FILLED, 'Musí byť vyplnený');
        $f->addSubmit('btn', 'Použiť');

        $f->onSuccess[] = function ($form) {
            $values = $form->values;

            if ($values['coupon'] == 'palo') {
                // todo pavolbincik spravit valudaciu na kupony
                $this->model->update([
                    'coupon' => $values['coupon']
                ]);
            } else {
                $form->addError('Kupón je nesprávny');
            }

            $this->redrawControl();
        };
    }

}