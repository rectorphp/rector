<?php

namespace Rector\Tests\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Fixture;

final class SkipForeachThisIfClassImplementsIteratorAggregate implements \IteratorAggregate
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

    public function getItems(): array
    {
        $items = [];
        foreach($this as $item) {
        	$items[] = $item;
        }
        return $items;
    }
}
?>
