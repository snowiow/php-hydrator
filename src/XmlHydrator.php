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
     * @var string
     */
    private $property;
    /**
     * @var Hydration[]
     */
    private $hydrations = [];

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
    public function getHydratedObjects(): array
    {
        $objects = [];
        foreach ($this->hydrations as $hydration) {
            $objects[] = $hydration->getObject();
        }

        return $objects;
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
        if (!empty($this->property) && !empty($this->hydrations)) {
            $property = $this->resolver->normalize($this->property);
            end($this->hydrations)->assign($property, trim($text->nodeValue));
        }
    }

    /**
     * @param string $class
     */
    private function invoke(string $class)
    {
        $class     = $this->resolver->resolve($class);
        $hydration = new Hydration($class);

        $this->assign($hydration);
        $this->hydrations[] = $hydration;
    }

    /**
     * @param Hydration $hydration
     */
    private function assign(Hydration $hydration)
    {
        for ($i = count($this->hydrations) - 1; $i >= 0; $i--) {
            $property = $hydration->getReflection()->getShortName();
            if ($this->hydrations[$i]->assign($property, $hydration->getObject())) {
                break;
            }
        }
    }
}