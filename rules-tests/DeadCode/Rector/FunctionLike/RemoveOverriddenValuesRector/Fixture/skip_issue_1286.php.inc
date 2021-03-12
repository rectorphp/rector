<?php

namespace Rector\Tests\DeadCode\Rector\FunctionLike\RemoveOverriddenValuesRector\Fixture;

final class SkipIssue1286
{
    public function run()
    {
        function someFunction() {
            if (($node = 1) || ($node = 2)) {
            }
        }
    }
}
