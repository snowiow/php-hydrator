<?php

namespace Dgame\Hydrator;

use ReflectionClass;
use function Dgame\Wrapper\object;
use function Dgame\Wrapper\string;

/**
 * Class Hydrator
 * @package Dgame\Hydrator
 */
abstract class Hydrator
{
    /**
     * @var Hydration[]
     */
    private $hydrations = [];
    /**
     * @var HydrationScope
     */
    private $scope;

    /**
     * Hydrator constructor.
     */
    public function __construct()
    {
        $this->scope = new HydrationScope();
    }

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
     * @param array $classes
     *
     * @return null|object
     */
    final public function tryToInvokeOne(array $classes)
    {
        foreach ($classes as $class) {
            $object = $this->tryToInvoke($class);
            if ($object !== null) {
                return $object;
            }
        }

        return null;
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
            $this->append(new Hydration($object, $reflection));

            return $object;
        }

        return null;
    }

    /**
     * @param Hydration $hydration
     */
    private function append(Hydration $hydration)
    {
        $this->hydrations[] = $hydration;
        $this->scope->push($hydration);
    }

    /**
     * @param string $name
     * @param        $value
     *
     * @return bool
     */
    final protected function assign(string $name, $value): bool
    {
        $name = object($name)->getNamespaceInfo()->getClass();
        foreach ($this->scope->getHydrations() as $hydration) {
            if ($hydration->shouldAssign($name, $value) && $hydration->assign($name, $value)) {
                return true;
            }
        }

        return false;
    }

    /**-
     * @param string $name
     *
     * @return bool
     */
    final protected function isValidName(string $name): bool
    {
        return !empty($name) && string($name)->match('#^[a-z]+#i');
    }

    /**
     * @param object $object
     */
    final protected function reclaim($object)
    {
        $this->scope->popUntil($object);
    }
}