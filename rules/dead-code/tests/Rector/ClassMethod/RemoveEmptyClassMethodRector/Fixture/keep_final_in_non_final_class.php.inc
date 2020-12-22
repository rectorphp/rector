<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveEmptyClassMethodRector\Fixture;

abstract class KeepFinalInNonFinalClass
{
    final public function __construct()
    {
    }
}
