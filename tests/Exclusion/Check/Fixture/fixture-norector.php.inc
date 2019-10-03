<?php

namespace Rector\Tests\Rector\Exclusion\Check\Fixture2NoRector;


final class SomeController
{
    /**
     * @noRector Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector
     */
    public function foo()
    {

    }

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

?>
