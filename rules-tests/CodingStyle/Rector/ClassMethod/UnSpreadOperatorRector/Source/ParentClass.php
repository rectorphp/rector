<?php

declare(strict_types=1);

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector\Source;

class ParentClass
{
    public static function make(... $arguments): static
    {
        return new static();
    }
}