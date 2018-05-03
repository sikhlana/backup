<?php

namespace Sikhlana\Backup\Models;

abstract class Model implements \ArrayAccess
{
    /**
     * @var array
     */
    protected $attributes;

    public function __construct(array $attributes = [])
    {
        $this->attributes = json_decode(json_encode($attributes), true);
    }

    public function getAttribute($key)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }

        return null;
    }

    public function getAttributes(array $keys)
    {
        $return = [];

        foreach ($keys as $key) {
            if (! is_null($value = $this->getAttribute($key))) {
                $return[$key] = $value;
            }
        }

        return $return;
    }

    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function setAttributes(array $values)
    {
        foreach ($values as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    public function offsetExists($offset)
    {
        return ! is_null($this->getAttribute($offset));
    }

    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->setAttribute($offset, null);
    }

    public function __get($name)
    {
        return $this->getAttribute($name);
    }

    public function __set($name, $value)
    {
        return $this->setAttribute($name, $value);
    }

    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    public function __unset($name)
    {
        return $this->offsetUnset($name);
    }
}