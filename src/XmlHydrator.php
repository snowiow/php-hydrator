<?php

namespace Dgame\Hydrator;

use DOMDocument;
use DOMNode;
use DOMNodeList;
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
                $this->hydrateClass($node);
            } else if ($this->maybeProperty($node)) {
                $this->assignProperty($node->nodeName, $node->nodeValue);
                $this->assignAttributes($node);
            }
        }
    }

    /**
     * @param DOMNode $node
     */
    private function hydrateClass(DOMNode $node)
    {
        $object = $this->invokeNode($node);
        if ($object !== null) {
            $this->assignAttributes($node);
            $this->hydrateNodes($node->childNodes);
            $this->reclaim($object);
        } else {
            $this->hydrateNodes($node->childNodes);
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
        if ($node->nodeType === XML_ELEMENT_NODE) {
            return $this->verifyChildNodes($node) || $node->hasAttributes();
        }

        return false;
    }

    /**
     * @param DOMNode $node
     *
     * @return bool
     */
    private function verifyChildNodes(DOMNode $node): bool
    {
        foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeType === XML_ELEMENT_NODE) {
                return true;
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
        $classes = $this->getClassNamesOf($node);

        return $this->tryToInvokeOne($classes);
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
        foreach (array_filter($names) as $class) {
            $output = array_merge($output, Resolver::instance()->getClassNamesOf($class));
        }

        return array_filter($output);
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