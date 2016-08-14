<?php

namespace Dgame\ObjectMapper;

use ReflectionClass;

/**
 * Class AttributeAssignment
 * @package Dgame\ObjectMapper
 */
final class AttributeAssignment
{
    const SETTERS_PREFIXES = [
        'set',
        'add',
        'append'
    ];

    /**
     * @var null|ReflectionClass
     */
    private $ref = null;
    /**
     * @var null|object
     */
    private $object = null;

    /**
     * AttributeHydrate constructor.
     *
     * @param ReflectionClass $ref
     */
    public function __construct(ReflectionClass $ref)
    {
        $this->ref    = $ref;
        $this->object = $ref->newInstance();
    }

    /**
     * @return null|object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param string $attribute
     * @param        $value
     *
     * @return bool
     */
    public function assign(string $attribute, $value) : bool
    {
        if ($this->assignViaMethod($attribute, $value)) {
            return true;
        }

        if ($this->ref->hasProperty($attribute) && $this->ref->getProperty($attribute)->isPublic()) {
            $this->object->{$attribute} = $value;

            return true;
        }

        if ($this->ref->hasMethod('__set')) {
            $this->object->__set($attribute, $value);

            return true;
        }

        return false;
    }

    /**
     * @param string $attribute
     * @param        $value
     *
     * @return bool
     */
    private function assignViaMethod(string $attribute, $value) : bool
    {
        foreach (self::SETTERS_PREFIXES as $prefix) {
            $method = $prefix . ucfirst($attribute);
            if ($this->ref->hasMethod($method)) {
                $parameters = $this->ref->getMethod($method)->getParameters();
                $class      = $parameters[0]->getClass();
                if (!empty($class) && is_array($value)) {
                    $mapper = new ObjectHydrate($class->name);
                    $value  = $mapper->hydrate($value);
                }

                $this->object->{$method}($value);

                return true;
            }
        }

        return false;
    }
}