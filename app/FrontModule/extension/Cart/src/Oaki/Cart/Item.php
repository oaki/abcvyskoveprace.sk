<?php

namespace Oaki\Cart;

use App\Model\PriceModel;
use Nette\Object;

class Item extends Object
{

    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $link;

    /** @var mixed */
    private $linkArgs = [];

    /** @var PriceModel */
    private $priceModel;

    /** @var int */
    private $vatRate;

    /** @var int */
    private $quantity;

    /** @var string */
    private $image;

    /** @var array */
    private $options = [];

    /** @var array */
    private $data = [];

    /** @var boolean */
    private $isSale;

    /** @var  ErrorMsg */
    private $errorMsg;

    private $msg;

    function __construct(
        ErrorMsg $errorMsg,
        $id,
        $quantity = 1,
        $isSale,
        PriceModel $price,
        array $options = []
    )
    {
//        $this->user = $user;
        $this->id       = $id;
        $this->errorMsg = $errorMsg;
        $this->setPriceModel($price);
        $this->setIsSale($isSale);
        $this->options = $options;

        // must be on the end
        $this->setQuantity($quantity);

    }

    public function addQuantity($quantity)
    {
        $this->quantity += (int)$quantity;

        $this->setQuantity($this->quantity);

        return $this;
    }

    public function getTotalPrice()
    {
        return $this->getQuantity() * $this->priceModel->price;
    }

    public function getTotalPriceWithTax()
    {
        return $this->getQuantity() * $this->priceModel->priceWithTax;
    }

    public function getPriceModel()
    {
        return $this->priceModel;
    }

    /**
     * @param float $priceModel
     *
     * @return Item
     */
    public function setPriceModel(PriceModel $priceModel)
    {
        $this->priceModel = $priceModel;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     *
     * @return Item
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $this->checkQuantity($quantity);

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Item
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Item
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     *
     * @return Item
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLinkArgs()
    {
        return $this->linkArgs;
    }

    /**
     * @param mixed $linkArgs
     *
     * @return Item
     */
    public function setLinkArgs($linkArgs)
    {
        $this->linkArgs = $linkArgs;

        return $this;
    }

    /**
     * @return int
     */
    public function getVatRate()
    {
        return (int)$this->vatRate;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $image
     *
     * @return Item
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return Item
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return Item
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isIsSale()
    {
        return $this->isSale;
    }

    /**
     * @param boolean $isSale
     *
     * @return Item
     */
    public function setIsSale($isSale)
    {
        $this->isSale = $isSale;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

    /**
     * @param mixed $errorMsg
     *
     * @return Item
     */
    public function setErrorMsg($errorMsg)
    {
        $this->errorMsg = $errorMsg;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * @param mixed $msg
     *
     * @return Item
     */
    public function setMsg($msg)
    {
        $this->msg = $msg;

        return $this;
    }

    public function hasMsg()
    {
        return ($this->msg !== null);
    }

    private function checkQuantity($quantity = 1)
    {
        $this->setMsg(null);

        return $quantity;
    }

}