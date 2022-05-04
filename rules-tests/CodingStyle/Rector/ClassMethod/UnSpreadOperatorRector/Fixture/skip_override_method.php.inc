<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector\Fixture;

use Rector\Tests\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector\Source\ParentClass;

final class SkipOverrideMethod extends ParentClass
{
    public static function make(... $arguments): static
    {
        return new static();
    }
}
?>
