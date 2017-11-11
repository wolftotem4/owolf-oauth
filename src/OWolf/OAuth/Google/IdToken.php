<?php

namespace OWolf\OAuth\Google;

use Illuminate\Contracts\Support\Arrayable;

class IdToken implements \ArrayAccess, Arrayable, \JsonSerializable
{
    /**
     * @var array
     */
    protected $attributes;

    /**
     * IdToken constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function offsetGet($offset)
    {
        return array_get($this->attributes, $offset);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->attributes);
    }

    public function offsetSet($offset, $value)
    {
        $this->attributes[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    /**
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return array_get($this->attributes, $key, $default);
    }

    /**
     * @param  string  $key
     * @param  mixed   $value
     * @return $this
     */
    public function set($key, $value)
    {
        array_set($this->attributes, $key, $value);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getId()
    {
        return $this->get('sub');
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->get('email');
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->get('name');
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}