<?php
namespace App\Model;

trait ArrayAccessTrait
{

    /**
     * @var array
     */
    protected $data;

    /**
     * Returns an iterator over all variables.
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        if (isset($this->data)) {
            return new \ArrayIterator($this->data);
        } else {
            return new \ArrayIterator;
        }
    }

    /**
     * Sets a variable
     *
     * @param  string  name
     * @param  mixed   value
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Gets a variable.
     *
     * @param  string    name
     *
     * @return mixed
     */
    public function &__get($name)
    {
        if ($this->__isset($name))
            return $this->data[$name];
    }

    /**
     * Determines whether a variable in this session section is set.
     *
     * @param  string    name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * Unsets a variable in this session section.
     *
     * @param  string    name
     *
     * @return void
     */
    public function __unset($name)
    {
        unset($this->data[$name], $this->meta[$name]);
    }

    /**
     * Sets a variable in this session section.
     *
     * @param  string  name
     * @param  mixed   value
     *
     * @return void
     */
    public function offsetSet($name, $value)
    {
        $this->__set($name, $value);
    }

    /**
     * Gets a variable from this session section.
     *
     * @param  string    name
     *
     * @return mixed
     */
    public function offsetGet($name)
    {
        return $this->data[$name];
    }

    /**
     * Determines whether a variable in this session section is set.
     *
     * @param  string    name
     *
     * @return bool
     */
    public function offsetExists($name)
    {
        return $this->__isset($name);
    }

    /**
     * Unsets a variable in this session section.
     *
     * @param  string    name
     *
     * @return void
     */
    public function offsetUnset($name)
    {
        $this->__unset($name);
    }
}