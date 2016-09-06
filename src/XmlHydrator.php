<?php

namespace Dgame\Hydrator;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMText;

/**
 * Class XmlHydrator
 * @package Dgame\Hydrator
 */
final class XmlHydrator
{
    /**
     * @var Resolver
     */
    private $resolver;
    /**
     * @var object[]
     */
    private $objects = [];
    /**
     * @var string
     */
    private $property;
    /**
     * @var Hydration
     */
    private $hydration;

    /**
     * XmlHydrator constructor.
     *
     * @param DOMDocument $document
     * @param Resolver    $resolver
     */
    public function __construct(DOMDocument $document, Resolver $resolver)
    {
        $this->resolver = $resolver;
        $this->hydrate($document);
    }

    /**
     * @return object[]
     */
    public function getObjects(): array
    {
        return $this->objects;
    }

    /**
     * @param DOMNode $node
     */
    private function hydrate(DOMNode $node)
    {
        if ($node->hasChildNodes()) {
            $this->traverse($node->childNodes);
        }
    }

    /**
     * @param DOMNodeList $list
     */
    private function traverse(DOMNodeList $list)
    {
        foreach ($list as $node) {
            switch ($node->nodeType) {
                case XML_ELEMENT_NODE:
                    $this->hydrateElementNode($node);
                    break;
                case XML_TEXT_NODE:
                    $this->hydrateTextNode($node);
                    break;
            }
        }
    }

    /**
     * @param DOMElement $element
     */
    private function hydrateElementNode(DOMElement $element)
    {
        if (class_exists($element->tagName)) {
            $this->invoke($element->tagName);
        } else {
            $this->property = trim($element->tagName);
        }

        $this->hydrate($element);
    }

    /**
     * @param DOMText $text
     */
    private function hydrateTextNode(DOMText $text)
    {
        if (!empty($this->property) && $this->hydration !== null) {
            $this->hydration->assign($this->property, trim($text->nodeValue));
        }
    }

    /**
     * @param string $class
     */
    private function invoke(string $class)
    {
        $hydration = new Hydration($this->resolver->resolve($class));
        if ($this->hydration !== null) {
            $this->hydration->assign($hydration->getReflection()->getShortName(), $hydration->getObject());
        }
        $this->objects[] = $hydration->getObject();
        $this->hydration = $hydration;
    }
}