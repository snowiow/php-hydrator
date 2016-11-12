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
     * @var Hydration[]
     */
    protected $hydrations = [];
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     *
     */
    final public function reset()
    {
        $this->errors     = [];
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
        if (!empty($class)) {
            if (class_exists($class)) {
                $reflection = new ReflectionClass($class);
                $object     = $reflection->newInstance();

                $this->assign($class, $object);
                $this->hydrations[] = new Hydration($object, $reflection);

                return $object;
            }

            $this->errors[] = sprintf('class "%s" does not exists', $class);
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
            $hydration = $this->hydrations[$i];
            if ($hydration->shouldAssign($name, $value) && $hydration->assign($name, $value)) {
                return true;
            }
        }

        $this->errors[] = sprintf('attribute "%s" was not assigned', $name);

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