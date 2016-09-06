<?php

namespace Dgame\Hydrator;

/**
 * Class Alias
 * @package Dgame\Hydrator
 */
final class Alias
{
    /**
     * @var Aliasable
     */
    private $aliasable;
    /**
     * @var string
     */
    private $class;
    /**
     * @var string
     */
    private $alias;

    /**
     * Alias constructor.
     *
     * @param string    $class
     * @param Aliasable $aliasable
     */
    public function __construct(string $class, Aliasable $aliasable)
    {
        $this->aliasable = $aliasable;
        $this->class     = $class;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function as (string $alias)
    {
        $this->alias = $alias;
        $this->aliasable->setAlias($this);
    }
}