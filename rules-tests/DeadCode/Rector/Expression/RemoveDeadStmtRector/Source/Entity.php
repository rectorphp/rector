<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\Expression\RemoveDeadStmtRector\Source;

use InvalidArgumentException;

class Entity
{
    public function __get($name)
    {
        if ($name === 'invalid') {
            throw new InvalidArgumentException();
        }
    }
}
