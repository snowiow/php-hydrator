<?php

namespace Dgame\Hydrator;

use function Dgame\Wrapper\string;
use Exception;
use ReflectionClass;

/**
 * Class Hydration
 * @package Dgame\Hydrator
 */
final class Hydration
{
    const PREFIXES = ['set', 'add', 'append'];

    /**
     * @var ReflectionClass
     */
    private $reflection;
    /**
     * @var object
     */
    private $object;

    /**
     * Hydration constructor.
     *
     * @param                 $object
     * @param ReflectionClass $reflection
     *
     * @throws Exception
     */
    public function __construct($object, ReflectionClass $reflection)
    {
        if (!$reflection->isInstance($object)) {
            throw new Exception('Invalid object');
        }

        $this->reflection = $reflection;
        $this->object     = $object;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param string $name
     * @param        $value
     *
     * @return bool
     */
    public function assign(string $name, $value): bool
    {
        return $this->assignByProperty($name, $value) || $this->assignByMethod($name, $value);
    }

    /**
     * @param string $name
     * @param        $value
     *
     * @return bool
     */
    private function assignByProperty(string $name, $value): bool
    {
        if ($this->reflection->hasProperty($name)) {
            $property = $this->reflection->getProperty($name);
            if ($property->isPublic()) {
                $property->setValue($this->object, $value);

                return true;
            }
        }

        return false;
    }

    /**
     * @param string $name
     * @param        $value
     *
     * @return bool
     */
    private function assignByMethod(string $name, $value): bool
    {
        $name = string($name)->toUpperFirst()->get();
        foreach (self::PREFIXES as $prefix) {
            $method = $prefix . $name;
            if ($this->reflection->hasMethod($method)) {
                $method = $this->reflection->getMethod($method);
                if ($method->isPublic()) {
                    $method->invoke($this->object, $value);

                    return true;
                }
            }
        }

        return false;
    }
}