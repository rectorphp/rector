<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector\Fixture;

abstract class KeepFinalInNonFinalClass
{
    final public function __construct()
    {
    }
}
