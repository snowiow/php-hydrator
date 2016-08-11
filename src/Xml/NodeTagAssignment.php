<?php

namespace Dgame\ObjectMapper\Xml;
use DOMNamedNodeMap;

/**
 * Class NodeTagAssignment
 * @package Dgame\ObjectMapper\Xml
 */
final class NodeTagAssignment
{
    /**
     * @var null|string
     */
    private $tag = null;
    /**
     * @var array
     */
    private $output = [];

    /**
     * NodeTagAssignment constructor.
     *
     * @param string $tag
     */
    public function __construct(string $tag)
    {
        $this->tag = $tag;
    }

    /**
     * @return array
     */
    public function getOutput(): array
    {
        return $this->output;
    }

    /**
     * @param array $nodes
     */
    public function assignNodes(array $nodes)
    {
        if (array_key_exists($this->tag, $this->output)) {
            $this->output[$this->tag] = array_merge($this->output[$this->tag], $nodes);
        } else {
            $this->output[$this->tag] = $nodes;
        }
    }

    /**
     * @param string $value
     */
    public function assignValue(string $value)
    {
        $value = trim($value);
        if (!empty($value)) {
            $this->output[$this->tag] = $value;
        }
    }

    /**
     * @param DOMNamedNodeMap $attributes
     */
    public function assignAttributes(DOMNamedNodeMap $attributes)
    {
        foreach ($attributes as $attr => $node) {
            $this->output[$this->tag]['@attributes'][$attr] = (string) $node->value;
        }
    }
}