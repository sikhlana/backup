<?php

namespace Sikhlana\Backup\Models;

abstract class Model
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
}