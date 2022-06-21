<?php

namespace Rector\Tests\Php81\Rector\Array_\FirstClassCallableRector\Fixture;

final class SkipFunctionReference
{
    public function run()
    {
        $name = [class_exists($this), 'name'];
    }

    public function name()
    {
    }
}
