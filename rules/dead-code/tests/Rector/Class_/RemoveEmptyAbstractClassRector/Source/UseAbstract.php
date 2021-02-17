<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\Source;

final class UseAbstract
{
    public function __construct(?AbstractClass $class = null)
    {
        if ($class) {

        }
    }
}