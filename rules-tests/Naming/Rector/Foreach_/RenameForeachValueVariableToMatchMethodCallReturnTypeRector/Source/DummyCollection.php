<?php

declare(strict_types=1);

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector\Source;

use ArrayAccess;
use Countable;
use IteratorAggregate;

final class DummyCollection implements Countable, IteratorAggregate, ArrayAccess
{
    public function getIterator(): void
    {
    }

    public function offsetExists($offset): void
    {
    }

    public function offsetGet($offset): void
    {
    }

    public function offsetSet($offset, $value): void
    {
    }

    public function offsetUnset($offset): void
    {
    }

    public function count(): void
    {
    }
}
