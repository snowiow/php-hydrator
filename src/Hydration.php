<?php

namespace Dgame\Hydrator;

use Exception;
use ReflectionClass;
use function Dgame\Wrapper\string;

/**
 * Class Hydration
 * @package Dgame\Hydrator
 */
final class Hydration
{
    /**
     * @var ReflectionClass
     */
    private $reflection;
    /**
     * @var object
     */
    private $object;
    /**
     * @var Resolver
     */
    private $resolver;

    /**
     * Hydration constructor.
     *
     * @param                 $object
     * @param ReflectionClass $reflection
     * @param Resolver        $resolver
     *
     * @throws Exception
     */
    public function __construct($object, ReflectionClass $reflection, Resolver $resolver)
    {
        if (!$reflection->isInstance($object)) {
            throw new Exception('Invalid object');
        }

        $this->reflection = $reflection;
        $this->object     = $object;
        $this->resolver   = $resolver;
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
        foreach ($this->gatherMethods($name) as $method) {
            if ($this->tryToInvoke($method, $value)) {
                return true;
            }
        }

        if ($this->resolver->isMagicAllowed()) {
            return $this->tryToInvoke('__set', $name, $value);
        }

        return false;
    }

    /**
     * @param string $name
     *
     * @return array
     */
    private function gatherMethods(string $name): array
    {
        $name = string($name)->upperCaseFirst()->get();

        $methods = [];
        foreach ($this->resolver->getPrefixes() as $prefix) {
            $methods[] = $prefix . $name;
        }

        return array_merge($methods, $this->resolver->getMethods());
    }

    /**
     * @param string $method
     * @param array  ...$args
     *
     * @return bool
     */
    private function tryToInvoke(string $method, ...$args): bool
    {
        if ($this->reflection->hasMethod($method)) {
            $method = $this->reflection->getMethod($method);
            if ($method->isPublic()) {
                $method->invoke($this->object, ...$args);

                return true;
            }
        }

        return false;
    }
}