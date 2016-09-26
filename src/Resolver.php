<?php

namespace Dgame\Hydrator;

/**
 * Class Resolver
 * @package Dgame\Hydrator
 */
final class Resolver
{
    /**
     * @var string
     */
    private $namespacePath;
    /**
     * @var array
     */
    private $aliase = [];

    /**
     * XmlHydrator constructor.
     *
     * @param string|null $namespacePath
     */
    public function __construct(string $namespacePath = null)
    {
        $this->namespacePath = rtrim(trim($namespacePath), '\\');
    }

    /**
     * @return string
     */
    public function getNamespacePath(): string
    {
        return $this->namespacePath;
    }

    /**
     * @param array $aliase
     */
    public function alias(array $aliase)
    {
        $this->aliase = $aliase;
    }

    /**
     * @param string $class
     *
     * @return string
     */
    public function resolve(string $class): string
    {
        return array_key_exists($class, $this->aliase) ? $this->aliase[$class] : $class;
    }
}