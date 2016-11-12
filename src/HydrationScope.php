<?php

namespace Dgame\Hydrator;

use Dgame\Optional\Optional;
use function Dgame\Optional\maybe;

/**
 * Class HydrationScope
 * @package Dgame\Hydrator
 */
final class HydrationScope
{
    /**
     * @var Hydration[]
     */
    private $hydrations = [];

    /**
     * @param Hydration $hydration
     */
    public function push(Hydration $hydration)
    {
        $this->hydrations[] = $hydration;
    }

    /**
     * @return Optional
     */
    public function pop(): Optional
    {
        return maybe(array_pop($this->hydrations));
    }

    /**
     * @return \Generator
     */
    public function getHydrations(): \Generator
    {
        for ($i = count($this->hydrations) - 1; $i >= 0; $i--) {
            yield $this->hydrations[$i];
        }
    }

    /**
     * @param object $object
     */
    public function popUntil($object)
    {
        if (!is_object($object)) {
            return;
        }

        foreach ($this->getHydrations() as $hydration) {
            if ($hydration->getObject() === $object) {
                $this->pop();
                break;
            }

            $this->pop();
        }
    }
}