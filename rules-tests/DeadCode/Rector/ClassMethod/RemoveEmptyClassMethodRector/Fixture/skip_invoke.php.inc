<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector\Fixture;

/**
 * Invokable class can be used for stub in test
 */
class SkipInvoke
{
    public function __invoke()
    {
    }
}

?>
