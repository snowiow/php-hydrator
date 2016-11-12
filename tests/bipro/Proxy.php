<?php

class Proxy
{
    private $attributes;

    public function __set(string $name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function __get(string $name)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        return null;
    }
}