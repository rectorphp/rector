<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

use Ds\Vector;
use IteratorAggregate;

/**
 * @template TValue
 *
 * @implements IteratorAggregate<int, TValue>
 */
final class ImmutableVector implements IteratorAggregate
{
    /**
     * @return iterable<TValue>|null
     */
    public function getVector(): ?iterable
    {
    }
}
?>
