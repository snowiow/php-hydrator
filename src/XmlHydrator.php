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
     * @var string
     */
    private $namespacePath;
    /**
     * @var array
     */
    private $aliase = [];
    /**
     * @var Hydration[]
     */
    private $hydrations = [];

    /**
     * XmlHydrator constructor.
     *
     * @param string|null $namespacePath
     */
    public function __construct(string $namespacePath = null)
    {
        $this->namespacePath = rtrim(trim($namespacePath), '\\');
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
     * @param array $aliase
     */
    public function alias(array $aliase)
    {
        $this->aliase = $aliase;
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
        foreach ($this->assembleClassNames($node->nodeName) as $class) {
            if (class_exists($class)) {
                $reflection = new ReflectionClass($class);
                $object     = $reflection->newInstance();

                $this->assign($class, $object);
                $this->hydrations[] = new Hydration($object, $reflection);

                return $object;
            }
        }

        return null;
    }

    /**
     * @param string $class
     *
     * @return array
     */
    public function assembleClassNames(string $class): array
    {
        $output = [];
        foreach (explode(':', $class) as $item) {
            $name = $this->extractName($item);
            if ($name !== null) {
                $output[] = $this->resolve($name);
            }
        }

        if (count($output) > 1) {
            return [
                implode('\\', $output),
                array_pop($output)
            ];
        }

        return $output;
    }

    /**
     * @param string $class
     *
     * @return string
     */
    private function resolve(string $class): string
    {
        return array_key_exists($class, $this->aliase) ? $this->aliase[$class] : $class;
    }

    /**
     * @param string $name
     * @param        $value
     *
     * @return bool
     */
    private function assign(string $name, $value): bool
    {
        $name = $this->extractName($name);
        if ($name === null) {
            return false;
        }

        for ($i = count($this->hydrations) - 1; $i >= 0; $i--) {
            if ($this->hydrations[$i]->assign($name, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $name
     *
     * @return null|string
     */
    private function extractName(string $name)
    {
        if (preg_match('#([a-z_]+[\w_]*)$#iS', $name, $matches)) {
            return trim($matches[1]);
        }

        return null;
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