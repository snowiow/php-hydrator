<?php

namespace Dgame\Hydrator;

use function Dgame\Wrapper\string;
use ReflectionClass;

/**
 * Class Hydrator
 * @package Dgame\Hydrator
 */
abstract class Hydrator
{
    /**
     * @var Hydration[]
     */
    protected $hydrations = [];

    /**
     *
     */
    final public function reset()
    {
        $this->hydrations = [];
    }

    /**
     * @return object[]
     */
    final public function getHydratedObjects(): array
    {
        $objects = [];
        foreach ($this->hydrations as $hydration) {
            $objects[] = $hydration->getObject();
        }

        return $objects;
    }

    /**
     * @param string $class
     *
     * @return null|object
     */
    final public function tryToInvoke(string $class)
    {
        if (class_exists($class)) {
            $reflection = new ReflectionClass($class);
            $object     = $reflection->newInstance();

            $this->assign($class, $object);
            $this->hydrations[] = new Hydration($object, $reflection);

            return $object;
        }

        return null;
    }

    /**
     * @param string $name
     * @param        $value
     *
     * @return bool
     */
    final protected function assign(string $name, $value): bool
    {
        $name = string($name)->namespaceInfo()->getClass();
        for ($i = count($this->hydrations) - 1; $i >= 0; $i--) {
            $hydration = $this->hydrations[$i];
            if ($hydration->shouldAssign($name, $value) && $hydration->assign($name, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    final protected function isValidName(string $name): bool
    {
        return !empty($name) && preg_match('#^[a-z]+#i', $name) === 1;
    }
}