<?php

namespace Nested;

class Schema
{
    public $complex;
    private $elements = [];

    public function appendElement(Element $element)
    {
        $this->elements[] = $element;
    }

    /**
     * @return Element[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }
}