<?php

declare(strict_types=1);


namespace Rector\Tests\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Source;

use Traversable;

final class IteratorAggregateImplementation implements \IteratorAggregate
{
    private array $items;

    public function __construct()
    {
        $this->items = [new \stdClass()];
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->items);
    }
}
