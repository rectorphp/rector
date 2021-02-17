<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveEmptyAbstractClassRector\Source;

abstract class AbstractClass
{}

final class UseAbstract
{
    public function __construct(?AbstractClass $class = null)
    {
        if ($class) {

        }
    }
}