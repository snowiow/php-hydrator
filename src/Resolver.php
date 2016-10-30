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
    const PREFIXES = ['set', 'add', 'append'];

    /**
     * @var string
     */
    private $namespacePath;
    /**
     * @var \Dgame\Wrapper\ArrayWrapper
     */
    private $aliase;
    /**
     * @var \Dgame\Wrapper\ArrayWrapper
     */
    private $prefixes;
    /**
     * @var \Dgame\Wrapper\ArrayWrapper
     */
    private $methods;
    /**
     * @var bool
     */
    private $magic = true;

    /**
     * XmlHydrator constructor.
     *
     * @param string|null $namespacePath
     */
    public function __construct(string $namespacePath = null)
    {
        $this->aliase        = assoc([]);
        $this->prefixes      = assoc(self::PREFIXES);
        $this->methods       = assoc([]);
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
        $this->aliase = assoc($aliase);

        return $this;
    }

    /**
     * @param array $aliase
     *
     * @return Resolver
     */
    public function appendAliase(array $aliase): Resolver
    {
        $this->aliase = $this->aliase->merge($aliase);

        return $this;
    }

    /**
     * @param array $prefixes
     *
     * @return Resolver
     */
    public function setPrefixes(array $prefixes): Resolver
    {
        $this->prefixes = $this->prefixes->merge($prefixes);

        return $this;
    }

    /**
     * @param array $prefixes
     *
     * @return Resolver
     */
    public function appendPrefixes(array $prefixes): Resolver
    {
        $this->prefixes = $this->prefixes->merge($prefixes);

        return $this;
    }

    /**
     * @return array
     */
    public function getPrefixes(): array
    {
        return $this->prefixes->get();
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
        return $this->methods->get();
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
     * @return string
     */
    public function resolve(string $class): string
    {
        $class = $this->aliase->valueOf($class)->default($class);
        if (empty($this->namespacePath)) {
            return $class;
        }

        return string('%s\\%s')->format($this->namespacePath, $class)->get();
    }
}