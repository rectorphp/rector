<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

final class SkipPrivateConstructor
{
    private function __construct()
    {
    }
}
