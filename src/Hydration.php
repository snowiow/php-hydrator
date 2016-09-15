<?php

namespace Dgame\Hydrator;

use function Dgame\Type\typeof;
use ReflectionClass;
use TypeError;

/**
 * Class Hydration
 * @package Dgame\Hydrator
 */
final class Hydration
{
    const PREFIXES = ['set', 'add', 'append'];

    /**
     * @var ReflectionClass
     */
    private $reflection;
    /**
     * @var object
     */
    private $object;

    /**
     * Hydration constructor.
     *
     * @param string $class
     */
    public function __construct(string $class)
    {
        $this->reflection = new ReflectionClass($class);
        $this->object     = $this->reflection->newInstance();
    }

    /**
     * @return ReflectionClass
     */
    public function getReflection(): ReflectionClass
    {
        return $this->reflection;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param string $property
     * @param        $value
     *
     * @return bool
     */
    public function assign(string $property, $value)
    {
        if (!empty($value)) {
            return $this->assignByProperty($property, $value) || $this->assignByMethod($property, $value);
        }

        return false;
    }

    /**
     * @param string $attribute
     * @param        $value
     *
     * @return bool
     */
    public function assignByProperty(string $attribute, $value): bool
    {
        if ($this->reflection->hasProperty($attribute)) {
            $property = $this->reflection->getProperty($attribute);
            if ($property->isPublic()) {
                $property->setValue($this->object, $value);

                return true;
            }
        }

        return false;
    }

    /**
     * @param string $property
     * @param        $value
     *
     * @return bool
     */
    public function assignByMethod(string $property, $value): bool
    {
        $property = ucfirst($property);
        foreach (self::PREFIXES as $prefix) {
            $method = $prefix . $property;
            if ($this->reflection->hasMethod($method)) {
                $method = $this->reflection->getMethod($method);
                if ($method->isPublic() && $method->getNumberOfParameters() === 1) {
                    $parameter = $method->getParameters()[0];
                    if ($parameter->hasType()) {
                        $type = (string) $parameter->getType();
                        if (typeof($value)->isImplicit($type)) {
                            $method->invoke($this->object, $value);

                            return true;
                        }

                        return false;
                    }

                    $method->invoke($this->object, $value);

                    return true;
                }
            }
        }

        return false;
    }
}