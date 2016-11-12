<?php

namespace Dgame\Hydrator;

use DOMDocument;
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
        foreach ($nodes as $node) {
            if ($this->maybeClass($node)) {
                $this->invokeNode($node);
                $this->hydrateNodes($node->childNodes);
            } else if ($this->maybeProperty($node)) {
                $this->assignProperty($node->nodeName, $node->nodeValue);
            }

            $this->assignAttributes($node);
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
        foreach ($this->getClassNamesOf($node) as $class) {
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
    private function getClassNamesOf(DOMNode $node): array
    {
        $names = [
            string($node->nodeName)->after(':')->toUpperCaseFirst()->get(),
            string($node->nodeName)->explode(':')->map('ucfirst')->implode('\\')->get()
        ];

        $output = [];
        foreach ($names as $class) {
            $output = assoc($output)->filterEmpty()->merge(Resolver::instance()->getClassNamesOf($class))->get();
        }

        return $output;
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
     * @param DOMNode $node
     *
     * @return bool
     */
    private function assignAttributes(DOMNode $node): bool
    {
        if (!$node->hasAttributes()) {
            return false;
        }

        foreach ($node->attributes as $attribute) {
            $this->assignProperty($attribute->nodeName, $attribute->nodeValue);
        }

        return true;
    }
}