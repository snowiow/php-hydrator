<?php

namespace Dgame\Hydrator;

use Exception;
use ReflectionClass;
use function Dgame\Wrapper\assoc;
use function Dgame\Wrapper\string;
use SebastianBergmann\CodeCoverage\Report\PHP;

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
     * @var array
     */
    private $assigned = [];

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
        $result = $this->assignByProperty($name, $value) || $this->assignByMethod($name, $value);

        $this->assigned[$name] = $result;

        return $result;
    }

    /**
     * @param string $name
     * @param        $value
     *
     * @return bool
     */
    public function shouldAssign(string $name, $value): bool
    {
        return !$this->isAssigned($name) || is_numeric($value) || !empty($value);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isAssigned(string $name): bool
    {
        return assoc($this->assigned)->valueOf($name)->default(false);
    }

    /**
     * @param string $name
     * @param        $value
     *
     * @return bool
     */
    private function assignByProperty(string $name, $value): bool
    {
        $names = [
            string($name)->toLowerCaseFirst()->get(),
            string($name)->toUpperCaseFirst()->get()
        ];

        foreach ($names as $name) {
            if ($this->reflection->hasProperty($name)) {
                $property = $this->reflection->getProperty($name);
                if ($property->isPublic()) {
                    $property->setValue($this->object, $value);

                    return true;
                }
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

        if (Resolver::instance()->isMagicAllowed()) {
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
        $name = string($name)->toUpperCaseFirst()->get();

        $methods = [];
        foreach (Resolver::instance()->getPrefixes() as $prefix) {
            $methods[] = $prefix . $name;
        }

        return array_merge($methods, Resolver::instance()->getMethods());
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