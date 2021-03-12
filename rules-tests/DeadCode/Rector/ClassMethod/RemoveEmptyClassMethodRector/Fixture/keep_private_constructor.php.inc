<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector\Fixture;

final class KeepPrivateConstructor
{
    private function __construct()
    {

    }

    public static function someNamedConstructor(): self
    {
        return new self();
    }
}
