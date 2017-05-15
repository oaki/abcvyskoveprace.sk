<?php

namespace Oaki\Cart;

use App\Components\BaseControl;
use Nette\Application\UI\Form;

class CartControl extends BaseControl
{

    /** @var string */
    private $templateFile;

    /** @var Cart */
    private $cart;

    function __construct(Cart $cart)
    {
        $this->cart         = $cart;
        $this->templateFile = __DIR__ . '/templates/CartControl.latte';
    }

    public function render(array $options = [])
    {
        $this->template->setFile($this->templateFile);

        foreach ($options as $key => $value) {
            $this->template->$key = $value;
        }

        $this->template->cart = $this->cart;
        $this->template->render();
    }

    public function handleDelete($key)
    {
        $this->cart->delete($key);
        $this->redrawControl();
    }

    public function handleEmpty()
    {
        $this->cart->clear();
        $this->redrawControl();
    }
}