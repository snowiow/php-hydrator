<?php

namespace Dgame\Hydrator;

/**
 * Class AliasProcedure
 * @package Dgame\Hydrator
 */
final class AliasProcedure
{
    /**
     * @var Resolver
     */
    private $resolver;
    /**
     * @var string
     */
    private $alias;

    /**
     * AliasProcedure constructor.
     *
     * @param Resolver $resolver
     * @param string   $alias
     */
    public function __construct(Resolver $resolver, string $alias)
    {
        $this->resolver = $resolver;
        $this->alias    = $alias;
    }

    /**
     * @param string $class
     *
     * @return Resolver
     */
    public function for (string $class): Resolver
    {
        $this->resolver->appendAliase([$class => $this->alias]);

        return $this->resolver;
    }
}