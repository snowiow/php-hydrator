<?php

namespace Dgame\ObjectMapper;

use DOMDocument;
use DOMNode;

/**
 * Class XmlObjectMapper
 * @package Dgame\ObjectMapper
 */
final class XmlObjectMapper
{
    /**
     * @var array
     */
    private $attributes = [];

    /**
     * XmlObjectMapper constructor.
     *
     * @param DOMDocument $document
     */
    public function __construct(DOMDocument $document)
    {
        $this->attributes = [
            $document->documentElement->tagName => $this->extractAttributes($document->documentElement)
        ];
    }

    /**
     * @return array
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }

    /**
     * @param DOMNode $node
     *
     * @return array|string
     */
    private function extractAttributes(DOMNode $node)
    {
        $output = [];
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
                return ['@cdata' => trim($node->textContent)];

            case XML_TEXT_NODE:
                return trim($node->textContent);

            case XML_ELEMENT_NODE:
                $output = $this->traverseChilds($node);
                if (is_array($output)) {
                    // if only one node of its kind, assign it directly instead if array($value);
                    foreach ($output as $tag => $result) {
                        if (is_array($result) && count($result) === 1) {
                            $output[$tag] = $result[0];
                        }
                    }

                    $output = array_merge($output, $this->traverseAttributes($node));
                }
                break;
        }

        return $output;
    }

    /**
     * @param DOMNode $node
     *
     * @return array|string
     */
    private function traverseChilds(DOMNode $node)
    {
        $childs = [];
        // for each child node, call the covert function recursively
        for ($i = 0, $c = $node->childNodes->length; $i < $c; $i++) {
            $child  = $node->childNodes->item($i);
            $result = $this->extractAttributes($child);
            if (!empty($child->tagName)) {
                $tag = $child->tagName;
                // assume more nodes of same kind are coming
                if (!array_key_exists($tag, $childs)) {
                    $childs[$tag] = [];
                }

                $childs[$tag][] = $result;
            } else if (!empty($result)) {
                $childs = $result;
            }
        }

        return $childs;
    }

    /**
     * @param DOMNode $node
     *
     * @return array
     */
    private function traverseAttributes(DOMNode $node)
    {
        $attributes = [];
        // loop through the attributes and collect them
        if ($node->attributes->length !== 0) {
            $attributes = [];
            foreach ($node->attributes as $attrName => $attrNode) {
                $attributes[$attrName] = (string) $attrNode->value;
            }

            // if its an leaf node, store the value in @value instead of directly storing it.
            if (!is_array($attributes)) {
                $attributes = ['@value' => $attributes];
            }

            $attributes['@attributes'] = $attributes;
        }

        return $attributes;
    }
}