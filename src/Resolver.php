<?php

namespace Dgame\Hydrator;

/**
 * Class Resolver
 * @package Dgame\Hydrator
 */
final class Resolver implements Aliasable
{
    /**
     * @var array
     */
    private $aliase = [];

    /**
     * @param Alias $alias
     */
    public function setAlias(Alias $alias)
    {
        $this->aliase[$alias->getClass()] = $alias->getAlias();
    }

    /**
     * @param string $class
     *
     * @return Alias
     */
    public function use (string $class) : Alias
    {
        return new Alias($class, $this);
    }

    /**
     * @param string $class
     *
     * @return string
     */
    public function resolve(string $class): string
    {
        if (array_key_exists($class, $this->aliase)) {
            return $this->aliase[$class];
        }

        if (strpos($class, ':') !== false) {
            $class = substr(strrchr($class, ':'), 1);
        }

        return $class;
    }
}