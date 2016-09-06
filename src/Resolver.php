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
    public function use (string $class): Alias
    {
        return new Alias($class, $this);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function normalize(string $name): string
    {
        if (preg_match('#([a-z]+)$#iS', $name, $matches)) {
            $name = trim($matches[1]);
        }

        return $name;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function resolve(string $name): string
    {
        if (array_key_exists($name, $this->aliase)) {
            return $this->aliase[$name];
        }

        return $this->normalize($name);
    }
}