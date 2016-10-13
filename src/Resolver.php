<?php

namespace Dgame\Hydrator;

use function Dgame\Wrapper\assoc;
use function Dgame\Wrapper\string;

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
     * @var array
     */
    private $prefixes = ['set', 'add', 'append'];

    /**
     * XmlHydrator constructor.
     *
     * @param string|null $namespacePath
     */
    public function __construct(string $namespacePath = null)
    {
        $this->namespacePath = string($namespacePath)->rightTrim('\\')->trim()->get();
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
        $class = assoc($this->aliase)->hasKey($class) ? $this->aliase[$class] : $class;
        if (empty($this->namespacePath)) {
            return $class;
        }

        return string('%s\\%s')->format($this->namespacePath, $class);
    }

    /**
     * @param array $prefixes
     */
    public function setPrefixes(array $prefixes)
    {
        $this->prefixes = $prefixes;
    }

    /**
     * @param array $prefixes
     */
    public function appendPrefixes(array $prefixes)
    {
        $this->prefixes = assoc($this->prefixes)->mergeWith($prefixes)->get();
    }

    /**
     * @return array
     */
    public function getPrefixes(): array
    {
        return $this->prefixes;
    }
}