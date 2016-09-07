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
     * @param array    $attributes
     * @param Resolver $resolver
     */
    public function __construct(array $attributes, Resolver $resolver)
    {
        $this->resolver = $resolver;
        $this->traverse($attributes);
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
     * @param array $attributes
     */
    private function traverse(array $attributes)
    {
        foreach ($attributes as $attribute => $value) {
            $this->hydrate($attribute, $value);
        }
    }

    /**
     * @param string $attribute
     * @param        $value
     */
    private function hydrate(string $attribute, $value)
    {
        if (class_exists($attribute)) {
            $this->invoke($attribute);
        } else if (!empty($this->hydrations)) {
            $attribute = $this->resolver->normalize($attribute);
            end($this->hydrations)->assign($attribute, $value);
        }

        if (is_array($value)) {
            $this->traverse($value);
        }
    }

    /**
     * @param string $class
     */
    private function invoke(string $class)
    {
        $class     = $this->resolver->resolve($class);
        $hydration = new Hydration($class);

        $this->assign($hydration);
        $this->hydrations[] = $hydration;
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