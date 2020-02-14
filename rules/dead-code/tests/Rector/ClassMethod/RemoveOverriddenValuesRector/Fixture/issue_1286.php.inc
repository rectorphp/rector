<?php

namespace Rector\DeadCode\Tests\Rector\ClassMethod\RemoveOverriddenValuesRector\Fixture;

final class Issue1286
{
    public function run()
    {
        function someFunction() {
            if (($node = 1) || ($node = 2)) {
            }
        }
    }
}
