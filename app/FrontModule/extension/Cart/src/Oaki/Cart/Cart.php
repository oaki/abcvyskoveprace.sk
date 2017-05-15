<?php

namespace Oaki\Cart;

use App\Model\PriceModel;
use Nette\InvalidArgumentException;
use Nette\Object;
use Nette\Security\User;

class Cart extends Object
{

    /** @var Item[] */
    private $items;

    /**
     * @var User $user
     */
    private $user;

    private $errorMsg;

    public function __construct($items = [], User $user)
    {
        if (!($items instanceof \ArrayAccess and $items instanceof \IteratorAggregate) and !is_array($items)) {
            throw new InvalidArgumentException('Items must be array or ArrayAccess and IteratorAggregate.');
        }
        $this->items = $items;
        $this->user  = $user;

        $this->errorMsg = new ErrorMsg();
//        $this->errorMsg->setMinError('Minimanlen');
//        $this->errorMsg->setMaxError('Maximanlen');
    }

    public function addItem($id, $quantity = 1, $isSale, PriceModel $price,
                            array $options = [])
    {
        $key  = $this->createKey($id, $options);
        $item = $this->getItem($key);

        if ($item) {
            $item->addQuantity($quantity);
        } else {
            $item              = new Item($this->errorMsg, $id, $quantity, $isSale, $price, $options);
            $this->items[$key] = $item;

        }

        return $item;
    }

    private function createKey($id, $options = [])
    {
        $options = (array)$options;
        sort($options);

        return md5($id . '-' . serialize($options));
    }

    public function getItem($key)
    {
        return isset($this->items[$key]) ? $this->items[$key] : null;
    }

    public function update($key, $quantity)
    {
        if ($quantity <= 0) {
            $this->delete($key);
        } else {

            $item = isset($this->items[$key]) ? $this->items[$key] : null;
            if ($item) {
                $item->setQuantity($quantity);
            }
        }
    }

    public function delete($key)
    {
        unset($this->items[$key]);
    }

    public function clear()
    {
        foreach ($this->items as $k => $v) {
            $this->delete($k);
        }
    }

    public function isEmpty()
    {
        return $this->getQuantity() > 0 ? false : true;
    }

    public function getQuantity()
    {
        $quantity = 0;
        foreach ($this->items as $key => $item) {
            $quantity += $item->getQuantity();
        }

        return $quantity;
    }

    public function getUser()
    {
        return isset($this->user);
    }

    public function getTotalPrice()
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->getTotalPrice();
        }

        return $total;
    }

    public function getTotalPriceWithTax()
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->getTotalPriceWithTax();
        }

        return $total;
    }

    /**
     * @return Item[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param Item[] $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

}