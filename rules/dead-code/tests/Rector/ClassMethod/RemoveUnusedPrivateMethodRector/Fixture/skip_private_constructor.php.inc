<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

final class SkipPrivateConstructor
{
    private function __construct()
    {
    }
}
