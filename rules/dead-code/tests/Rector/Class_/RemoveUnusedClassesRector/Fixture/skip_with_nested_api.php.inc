<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveUnusedClassesRector\Fixture;

final class SkipWithNestedApi
{
    /**
     * @api
     */
    public function run()
    {
        return 1000;
    }
}
