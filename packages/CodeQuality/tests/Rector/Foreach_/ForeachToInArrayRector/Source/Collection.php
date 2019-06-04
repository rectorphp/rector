<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachToInArrayRector\Source;

use ArrayIterator;
use Closure;
use Doctrine\Common\Collections\Expr\ClosureExpressionVisitor;
use const ARRAY_FILTER_USE_BOTH;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_reverse;
use function array_search;
use function array_slice;
use function array_values;
use function count;
use function current;
use function end;
use function in_array;
use function key;
use function next;
use function reset;
use function spl_object_hash;
use function uasort;

class Collection implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
    }
}
