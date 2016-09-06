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
     * @var object[]
     */
    private $objects = [];
    /**
     * @var Hydration
     */
    private $hydration;

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
        return $this->objects;
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
        } else if ($this->hydration !== null) {
            $attribute = $this->resolver->normalize($attribute);
            $this->hydration->assign($attribute, $value);
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

        if ($this->hydration !== null) {
            $class = $hydration->getReflection()->getShortName();
            $this->hydration->assign($class, $hydration->getObject());
        }

        $this->objects[] = $hydration->getObject();
        $this->hydration = $hydration;
    }
}