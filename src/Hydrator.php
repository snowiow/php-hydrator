<?php

namespace Dgame\Hydrator;

use ReflectionClass;

/**
 * Class Hydrator
 * @package Dgame\Hydrator
 */
abstract class Hydrator
{
    /**
     * @var Resolver
     */
    protected $resolver;
    /**
     * @var Hydration[]
     */
    protected $hydrations = [];

    /**
     * XmlHydrator constructor.
     *
     * @param Resolver $resolver
     */
    public function __construct(Resolver $resolver)
    {
        $this->resolver = $resolver;
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
     * @param string $class
     *
     * @return null|object
     */
    final public function invoke(string $class)
    {
        if (class_exists($class)) {
            $reflection = new ReflectionClass($class);
            $object     = $reflection->newInstance();

            $this->assign($class, $object);
            $this->hydrations[] = new Hydration(
                $object,
                $reflection,
                $this->resolver
            );

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
        for ($i = count($this->hydrations) - 1; $i >= 0; $i--) {
            if ($this->hydrations[$i]->assign($name, $value)) {
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