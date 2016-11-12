<?php

/**
 * Created by PhpStorm.
 * User: Bjarne
 * Date: 12.11.2016
 * Time: 02:56
 */
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