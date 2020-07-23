<?php
declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\Fixture;

use Generator;
use IteratorAggregate;

class SkipIterator implements IteratorAggregate
{

    public function fetchAll(): array
    {
        $result = [];

        foreach ($this as $row) {
            $result[] = $row;
        }

        return $result;
    }

    public function getIterator(): Generator
    {
        yield 1;
        yield 2;
    }
}
