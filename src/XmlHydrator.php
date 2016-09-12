<?php

namespace Dgame\Hydrator;

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
     * @param Resolver $resolver
     */
    public function __construct(Resolver $resolver)
    {
        $this->resolver = $resolver;
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
     * @return Hydration
     */
    public function getLastHydratedObject(): Hydration
    {
        return end($this->hydrations);
    }

    /**
     * @param DOMNode $node
     */
    public function hydrate(DOMNode $node)
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

            $this->hydrateAttributes($node);
        }
    }

    /**
     * @param DOMElement $element
     */
    private function hydrateElementNode(DOMElement $element)
    {
        $class = $this->resolver->resolve($element->tagName);
        if (class_exists($class)) {
            $this->invoke($class);
        } else {
            $this->property = $class;
        }

        $this->hydrate($element);
    }

    /**
     * @param DOMText $text
     */
    private function hydrateTextNode(DOMText $text)
    {
        if (!empty($this->property) && !empty($this->hydrations)) {
            $this->getLastHydratedObject()->assign($this->property, trim($text->nodeValue));
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

    /**
     * @param DOMNode $node
     */
    private function hydrateAttributes(DOMNode $node)
    {
        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attribute) {
                $this->assignAttribute($attribute);
            }
        }
    }

    /**
     * @param DOMNode $node
     */
    private function assignAttribute(DOMNode $node)
    {
        $property = $this->resolver->resolve($node->nodeName);
        $this->getLastHydratedObject()->assign($property, trim($node->nodeValue));
    }
}