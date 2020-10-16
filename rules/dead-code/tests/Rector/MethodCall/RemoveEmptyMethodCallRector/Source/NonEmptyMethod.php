<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Source;

class NonEmptyMethod
{
    public function run()
    {
        echo 'run';
    }
}
