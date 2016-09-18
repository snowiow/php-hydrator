<?php

namespace Dgame\Hydrator;

/**
 * Interface Aliasable
 * @package Dgame\Hydrator
 */
interface Aliasable
{
    /**
     * @param Alias $alias
     */
    public function setAlias(Alias $alias);

    /**
     * @param string $class
     *
     * @return Alias
     */
    public function use (string $class): Alias;
}