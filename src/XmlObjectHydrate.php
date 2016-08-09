<?php

namespace Dgame\ObjectMapper;

use DOMDocument;

final class XmlObjectHydrate
{
    public function __construct(callable $autoload)
    {
        require_once '../test/Lieferung.php';

        spl_autoload_register($autoload);
    }

    public function hydrate(DOMDocument $document)
    {
        $mapper = new XmlObjectMapper($document);

        return $this->map($mapper->getAttributes());
    }

    private function map(array $attributes)
    {
        $objects = [];
        foreach ($attributes as $key => $values) {
            if (!is_array($values)) {
                continue;
            }

            if (class_exists($key)) {
                $hydrate       = new ObjectHydrate($key);
                $objects[$key] = $hydrate->hydrate($values);
            } else {
                $objects = array_merge($objects, $this->map($values));
            }
        }

        return $objects;
    }
}