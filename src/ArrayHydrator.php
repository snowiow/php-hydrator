<?php

namespace Dgame\Hydrator;

/**
 * Class ArrayHydrator
 * @package Dgame\Hydrator
 */
final class ArrayHydrator
{
    /**
     * @var Resolver
     */
    private $resolver;
    /**
     * @var Hydration[]
     */
    private $hydrations;

    /**
     * ArrayHydrator constructor.
     *
     * @param Resolver $resolver
     */
    public function __construct(Resolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @return object[]
     */
    public function getHydratedObjects(): array
    {
        $objects = [];
        foreach ($this->hydrations as $hydration) {
            $objects[] = $hydration->getObject();
        }

        return $objects;
    }

    /**
     * @param array $data
     */
    public function hydrate(array $data)
    {
        foreach ($data as $key => $value) {
            if (!is_string($key)) {
                if (is_array($value)) {
                    $this->hydrate($value);
                }

                continue;
            }

            if (class_exists($key) && is_array($value)) {
                $value = $this->hydrateClass($key, $value);
            }

            if (!empty($this->hydrations)) {
                end($this->hydrations)->assign($key, $value);
            }
        }
    }

    /**
     * @param string $class
     * @param array  $values
     *
     * @return Hydration
     */
    public function hydrateClass(string $class, array $values): Hydration
    {
        $hydration = $this->invoke($class);

        return $this->hydrateObject($hydration, $values);
    }

    /**
     * @param Hydration $hydration
     * @param array     $values
     *
     * @return Hydration
     */
    public function hydrateObject(Hydration $hydration, array $values): Hydration
    {
        foreach ($values as $key => $value) {
            if (is_string($key)) {
                if (class_exists($key) && is_array($value)) {
                    $value = $this->hydrateClass($key, $value);
                }

                $hydration->assign($key, $value);
            } else if (is_array($value)) {
                $this->hydrate($value);
            }
        }

        return $hydration;
    }

    /**
     * @param string $class
     *
     * @return Hydration
     */
    private function invoke(string $class): Hydration
    {
        $class     = $this->resolver->resolve($class);
        $hydration = new Hydration($class);

        $this->assign($hydration);
        $this->hydrations[] = $hydration;

        return $hydration;
    }

    /**
     * @param Hydration $hydration
     */
    private function assign(Hydration $hydration)
    {
        for ($i = count($this->hydrations) - 1; $i >= 0; $i--) {
            $property = $hydration->getReflection()->getShortName();
            if ($this->hydrations[$i]->assign($property, $hydration->getObject())) {
                break;
            }
        }
    }
}