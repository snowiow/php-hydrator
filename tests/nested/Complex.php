<?php

namespace Nested;

class Complex
{
    public $sequence;
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