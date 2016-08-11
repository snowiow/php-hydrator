<?php

namespace Dgame\ObjectMapper\Xml;

use DOMDocument;
use DOMElement;

/**
 * Class NodeProcessor
 * @package Dgame\ObjectMapper\Xml
 */
final class NodeProcessor
{
    /**
     * @var array
     */
    private $output = [];

    /**
     * NodeProcessor constructor.
     *
     * @param DOMDocument $document
     */
    public function __construct(DOMDocument $document)
    {
        $this->output = $this->process($document->documentElement);
    }

    /**
     * @return array
     */
    public function getOutput() : array
    {
        return $this->output;
    }

    /**
     * @param DOMElement $element
     *
     * @return array
     */
    private function process(DOMElement $element) : array
    {
        $assignment = new NodeTagAssignment($element->tagName);
        foreach ($element->childNodes as $child) {
            switch ($child->nodeType) {
                case XML_ELEMENT_NODE:
                    $nodes = $this->process($child);
                    $assignment->assignNodes($nodes);
                    break;
                case XML_TEXT_NODE:
                    $assignment->assignValue($child->textContent);
                    break;
            }
        }

        $assignment->assignAttributes($element->attributes);

        return $assignment->getOutput();
    }
}