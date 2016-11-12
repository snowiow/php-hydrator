<?php

namespace Dgame\Hydrator;

use function Dgame\Type\typeof;

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
        //        print 'Class: ' . $class . PHP_EOL;
        $this->tryToInvoke($class);
        $this->hydrateAttributes($class, $attributes);
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
            } else if (typeof($value)->isArray()) {
                $this->hydrateClass($class, $value);
            }
        }
    }

    /**
     * @param string $attribute
     * @param        $value
     *
     * @return bool|void
     */
    private function hydrateAttribute(string $attribute, $value)
    {
        if ($this->isSingleClass($value)) {
            return $this->hydrateClass($attribute, $value);
        }

        if (typeof($value)->isArray()) {
            return $this->hydrateAttributes($attribute, $value);
        }

        return $this->assign($attribute, $value);
    }

    /**
     * @param $data
     *
     * @return bool
     */
    private function isSingleClass($data): bool
    {
        if (typeof($data)->isArray()) {
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