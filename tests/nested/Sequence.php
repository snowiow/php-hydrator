<?php

namespace Nested;

class Sequence
{
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