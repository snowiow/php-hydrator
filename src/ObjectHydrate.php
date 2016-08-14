<?php

namespace Dgame\ObjectMapper;

use ReflectionClass;

/**
 * Class ObjectHydrate
 * @package Dgame\ObjectMapper
 */
final class ObjectHydrate
{
    /**
     * @var null|ReflectionClass
     */
    private $ref = null;

    /**
     * ObjectHydrate constructor.
     *
     * @param string $class
     */
    public function __construct(string $class)
    {
        $this->ref = new ReflectionClass($class);
    }

    /**
     * @param array $attributes
     *
     * @return null|object
     */
    public function hydrate(array $attributes)
    {
        $assignment = new AttributeAssignment($this->ref);
        foreach ($attributes as $attr => $value) {
            $attr = preg_replace('#\s+#', '_', $attr);
            $attr = preg_replace_callback('#(_[a-z])#i', function(array $matches) {
                return ucfirst($matches[0][1]);
            }, $attr);

            $assignment->assign($attr, $value);
        }

        return $assignment->getObject();
    }
}