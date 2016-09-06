<?php

namespace Dgame\Hydrator;

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
     * @param string $class
     */
    public function __construct(string $class)
    {
        $this->reflection = new ReflectionClass($class);
        $this->object     = $this->reflection->newInstance();
    }

    /**
     * @return ReflectionClass
     */
    public function getReflection(): ReflectionClass
    {
        return $this->reflection;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param string $property
     * @param        $value
     *
     * @return bool
     */
    public function assign(string $property, $value)
    {
        if (!empty($value)) {
            return $this->assignByProperty($property, $value) || $this->assignByMethod($property, $value);
        }

        return false;
    }

    /**
     * @param string $property
     * @param        $value
     *
     * @return bool
     */
    private function assignByProperty(string $property, $value): bool
    {
        if ($this->reflection->hasProperty($property) && $this->reflection->getProperty($property)->isPublic()) {
            $this->reflection->getProperty($property)->setValue($this->object, $value);

            return true;
        }

        return false;
    }

    /**
     * @param string $property
     * @param        $value
     *
     * @return bool
     */
    private function assignByMethod(string $property, $value): bool
    {
        $property = ucfirst($property);
        foreach (self::PREFIXES as $prefix) {
            $method = $prefix . $property;
            if ($this->reflection->hasMethod($method) && $this->reflection->getMethod($method)->isPublic()) {
                $this->reflection->getMethod($method)->invoke($this->object, $value);

                return true;
            }
        }

        return false;
    }
}