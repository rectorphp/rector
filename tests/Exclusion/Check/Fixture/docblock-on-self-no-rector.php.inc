<?php

namespace Rector\Core\Tests\Exclusion\Check\Fixture;


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
