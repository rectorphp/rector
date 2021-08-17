<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\Expression\RemoveDeadStmtRector\Source;

use ArrayAccess;
use InvalidArgumentException;
use RuntimeException;

class SomeArrayAccess implements ArrayAccess
{
    public function offsetExists($offset)
    {
        return false;
    }

    public function offsetGet($offset)
    {
        if (! $this->offsetExists($offset)) {
            throw new InvalidArgumentException();
        }

        return true;
    }

    public function offsetSet($offset, $value)
    {
        throw new RuntimeException();
    }

    public function offsetUnset($offset)
    {
        throw new RuntimeException();
    }
}
