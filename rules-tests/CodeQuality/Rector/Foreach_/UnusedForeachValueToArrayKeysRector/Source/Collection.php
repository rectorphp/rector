<?php

declare(strict_types=1);

namespace Rector\Tests\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector\Source;

class Collection implements \IteratorAggregate
{
    public function getIterator()
    {
    }
}
