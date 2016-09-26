<?php

namespace Dgame\Hydrator;

use DOMDocument;
use DOMNamedNodeMap;
use DOMNode;
use DOMNodeList;
use ReflectionClass;

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
     *
     */
    public function reset()
    {
        $this->hydrations = [];
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
     * @param DOMDocument $document
     */
    public function hydrate(DOMDocument $document)
    {
        $this->hydrateNodes($document->childNodes);
    }

    /**
     * @param DOMNodeList $nodes
     */
    public function hydrateNodes(DOMNodeList $nodes)
    {
        for ($i = 0; $i < $nodes->length; $i++) {
            $node = $nodes->item($i);
            if ($this->maybeClass($node)) {
                $this->invoke($node);
                $this->hydrateNodes($node->childNodes);
            } else if ($this->maybeProperty($node)) {
                $this->assign($node->nodeName, $node->nodeValue);
            }

            if ($node->hasAttributes()) {
                $this->assignAttributes($node->attributes);
            }
        }
    }

    /**
     * @param DOMNode $node
     *
     * @return bool
     */
    public function maybeProperty(DOMNode $node): bool
    {
        if ($node->nodeType === XML_ELEMENT_NODE && $node->childNodes->length === 1) {
            return $node->firstChild->nodeType === XML_TEXT_NODE;
        }

        return false;
    }

    /**
     * @param DOMNode $node
     *
     * @return bool
     */
    public function maybeClass(DOMNode $node): bool
    {
        if ($node->nodeType === XML_ELEMENT_NODE && $node->childNodes->length > 0) {
            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeType === XML_ELEMENT_NODE) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param DOMNode $node
     *
     * @return null|object
     */
    public function invoke(DOMNode $node)
    {
        // string($node->nodeName)->replace([':' => '\\'])->get();
        $class = str_replace(':', '\\', $node->nodeName);
        $class = $this->resolver->resolve($class);
        if (class_exists($class)) {
            $reflection = new ReflectionClass($class);
            $object     = $reflection->newInstance();

            $this->assign($class, $object);
            $this->hydrations[] = new Hydration($object, $reflection);

            return $object;
        }

        return null;
    }

    /**
     * @param string $name
     * @param        $value
     *
     * @return bool
     */
    private function assign(string $name, $value): bool
    {
        //string($name)->contains(':', $pos) / string($name)->after(':')->default($name);
        if (($pos = strpos($name, ':')) !== false) {
            $name = substr($name, $pos + 1);
        }

        for ($i = count($this->hydrations) - 1; $i >= 0; $i--) {
            if ($this->hydrations[$i]->assign($name, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param DOMNamedNodeMap $attributes
     */
    private function assignAttributes(DOMNamedNodeMap $attributes)
    {
        for ($i = 0; $i < $attributes->length; $i++) {
            $attribute = $attributes->item($i);
            $this->assign($attribute->nodeName, $attribute->nodeValue);
        }
    }
}