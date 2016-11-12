<?php

namespace Bipro;

class Person
{
    public $type;
    private $name;

    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}