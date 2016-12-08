<?php

namespace Dgame\Hydrator;

/**
 * Class JsonHydrator
 * @package Dgame\Hydrator
 */
final class JsonHydrator extends Hydrator
{
    /**
     * @param string $class
     * @param array  $attributes
     */
    public function hydrate(string $class, array $attributes)
    {
        $this->hydrateClass($class, $attributes);
    }

    /**
     * @param string $class
     * @param array  $attributes
     */
    private function hydrateClass(string $class, array $attributes)
    {
        $object = $this->tryToInvokeOne(Resolver::instance()->getClassNamesOf($class));
        if ($object !== null) {
            $this->hydrateAttributes($class, $attributes);
        }
    }

    /**
     * @param string $class
     * @param array  $attributes
     */
    private function hydrateAttributes(string $class, array $attributes)
    {
        foreach ($attributes as $attribute => $value) {
            if ($this->isValidName($attribute)) {
                $this->hydrateAttribute($attribute, $value);
            } else if (is_array($value)) {
                $this->hydrateClass($class, $value);
            }
        }
    }

    /**
     * @param string $attribute
     * @param mixed  $value
     */
    private function hydrateAttribute(string $attribute, $value)
    {
        if ($this->isSingleClass($value)) {
            $this->hydrateClass($attribute, $value);
        } else if (is_array($value)) {
            $this->hydrateAttributes($attribute, $value);
        } else {
            $this->assign($attribute, $value);
        }
    }

    /**
     * @param $data
     *
     * @return bool
     */
    private function isSingleClass($data): bool
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (!$this->isValidName($key)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}