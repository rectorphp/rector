<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

final class SkipAssignExpressionClosureUseVariable
{
    public function run()
    {
        $content = [1, 2, 3];
        $filtered = array_filter([1, 2, 3], function ($v) use ($content) {
            return $content !== $v;
        });
        return $filtered;
    }
}
