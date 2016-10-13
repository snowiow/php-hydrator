<?php

namespace Dgame\Hydrator;

use DOMDocument;
use DOMNamedNodeMap;
use DOMNode;
use DOMNodeList;
use function Dgame\Wrapper\assoc;
use function Dgame\Wrapper\string;

/**
 * Class XmlHydrator
 * @package Dgame\Hydrator
 */
final class XmlHydrator extends Hydrator
{
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
                $this->invokeNode($node);
                $this->hydrateNodes($node->childNodes);
            } else if ($this->maybeProperty($node)) {
                $this->assignProperty($node->nodeName, $node->nodeValue);
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
            return $node->firstChild->nodeType === XML_TEXT_NODE && $this->isValidName($node->nodeName);
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
                    return $this->isValidName($node->nodeName);
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
    public function invokeNode(DOMNode $node)
    {
        foreach ($this->resolveNode($node) as $class) {
            $object = $this->invoke($class);
            if ($object !== null) {
                return $object;
            }
        }

        return null;
    }

    /**
     * @param DOMNode $node
     *
     * @return array
     */
    private function resolveNode(DOMNode $node): array
    {
        $classes = [
            string($node->nodeName)->after(':')->get(),
            string($node->nodeName)->replace([':' => '\\'])->get()
        ];

        return assoc($classes)->filterEmpty()->map([$this->resolver, 'resolve'])->get();
    }

    /**
     * @param string $name
     * @param        $value
     *
     * @return bool
     */
    private function assignProperty(string $name, $value): bool
    {
        $name = string($name)->after(':')->default($name)->get();

        return $this->assign($name, $value);
    }

    /**
     * @param DOMNamedNodeMap $attributes
     */
    private function assignAttributes(DOMNamedNodeMap $attributes)
    {
        for ($i = 0; $i < $attributes->length; $i++) {
            $attribute = $attributes->item($i);
            $this->assignProperty($attribute->nodeName, $attribute->nodeValue);
        }
    }
}