<?php

namespace Rector\Core\Tests\Exclusion\Fixture;

final class SkipDocblockOnSelfNoRector
{
    /**
     * @noRector \Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector
     */
    public function bar()
    {

    }

    /** @noRector \Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector */
    public function baz()
    {

    }
}
