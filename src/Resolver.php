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
     * @var Resolver
     */
    private static $instance;

    /**
     * @var array
     */
    private $namespaces = [];
    /**
     * @var array
     */
    private $aliase = [];
    /**
     * @var array
     */
    private $prefixes = ['set', 'add', 'append'];
    /**
     * @var array
     */
    private $methods = [];
    /**
     * @var bool
     */
    private $magic = true;

    /**
     * Resolver constructor.
     */
    private function __construct()
    {
    }

    /**
     * @return Resolver
     */
    public static function instance(): Resolver
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string $namespace
     *
     * @return Resolver
     */
    public function appendNamespace(string $namespace): Resolver
    {
        $namespace = string($namespace)->rightTrim('\\')->trim();
        if ($namespace->isNotEmpty()) {
            $this->namespaces[] = $namespace->get();
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * @param string $alias
     *
     * @return AliasProcedure
     */
    public function useAlias(string $alias): AliasProcedure
    {
        return new AliasProcedure($this, $alias);
    }

    /**
     * @param array $aliase
     *
     * @return Resolver
     */
    public function setAliase(array $aliase): Resolver
    {
        $this->aliase = $aliase;

        return $this;
    }

    /**
     * @param array $aliase
     *
     * @return Resolver
     */
    public function appendAliase(array $aliase): Resolver
    {
        $this->aliase = array_merge($this->aliase, $aliase);

        return $this;
    }

    /**
     * @param array $prefixes
     *
     * @return Resolver
     */
    public function setPrefixes(array $prefixes): Resolver
    {
        $this->prefixes = $prefixes;

        return $this;
    }

    /**
     * @param array $prefixes
     *
     * @return Resolver
     */
    public function appendPrefixes(array $prefixes): Resolver
    {
        $this->prefixes = array_merge($this->prefixes, $prefixes);

        return $this;
    }

    /**
     * @return array
     */
    public function getPrefixes(): array
    {
        return $this->prefixes;
    }

    /**
     * @param array $methods
     *
     * @return Resolver
     */
    public function setMethods(array $methods): Resolver
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @return Resolver
     */
    public function enableMagic(): Resolver
    {
        $this->magic = true;

        return $this;
    }

    /**
     * @return Resolver
     */
    public function disableMagic(): Resolver
    {
        $this->magic = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMagicAllowed(): bool
    {
        return $this->magic;
    }

    /**
     * @param string $class
     *
     * @return array
     */
    public function getClassNamesOf(string $class): array
    {
        $class = assoc($this->aliase)->valueOf($class)->default($class);
        $names = [$class];
        foreach ($this->namespaces as $namespace) {
            $names[] = string('%s\\%s')->format($namespace, $class)->get();
        }

        return $names;
    }
}