<?php

namespace WhatTheField\Node;

use \ArrayIterator;
use \ArrayAccess;
use \IteratorAggregate;
use \Countable;
use \InvalidArgumentException;

/**
 * A collection object for anything
 */
class Collection implements ArrayAccess, IteratorAggregate, Countable
{
    protected $container;

    public function __construct ($container)
    {
        if (is_array($container)) {
            $this->container = $container;
        } else {
            throw new \InvalidArgumentException(sprintf(
                'Unknown type given to Collection: %s',
                is_object($container) ? get_class($container) : gettype($container)
            ));
        }
    }

    /**
     * This function returns a new copy of the collection object,
     * but with new children.
     */
    protected function copyWithNewChildren($children)
    {
        return new self($children);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->container);
    }

    public function count()
    {
        return count($this->container);
    }

    public function offsetExists ($offset)
    {
        return isset($this->container[$offset]);
    }

    public function offsetGet ($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    public function offsetSet ($offset , $value)
    {
        throw new \RuntimeException('Collection is immutable.');
    }

    public function offsetUnset ($offset)
    {
        throw new \RuntimeException('Collection is immutable.');
    }

    public function __toString()
    {
        $out = '';
        foreach ($this->container as $node) {
            $out .= (string)$node;
        }
    }

    /**
     * Generic find function.
     */
    public function find(callable $matcher=null)
    {
        $match = [];
        foreach ($this as $child) {
            if ($matcher === null || $matcher($child) === true) {
                $match[] = $child->find();
            }
        }
        return $this->copyWithNewChildren($match);
    }
}