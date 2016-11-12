<?php

namespace Dgame\Hydrator;

use function Dgame\Type\typeof;

/**
 * Class ArrayHydrator
 * @package Dgame\Hydrator
 */
final class ArrayHydrator extends Hydrator
{
    /**
     * @param array $data
     */
    public function hydrate(array $data)
    {
        foreach ($data as $class => $attributes) {
            if ($this->maybeClass($class)) {
                $this->tryToInvoke($class);
            }

            $this->hydrateAttributes($attributes);
        }
    }

    /**
     * @param array $attributes
     */
    private function hydrateAttributes(array $attributes)
    {
        foreach ($attributes as $attribute => $value) {
            if ($this->maybeProperty($attribute)) {
                $this->assign($attribute, $value);
            } else if (typeof($value)->isArray()) {
                $this->hydrate($value);
            }
        }
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    private function maybeClass(string $class)
    {
        return $this->isValidName($class);
    }

    /**
     * @param string $property
     *
     * @return bool
     */
    private function maybeProperty(string $property)
    {
        return $this->isValidName($property);
    }
}