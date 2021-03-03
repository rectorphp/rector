<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Foreach_\UnusedForeachValueToArrayKeysRector\Source;

use IteratorAggregate;
class Collection implements IteratorAggregate
{
    public function getIterator()
    {
    }
}
