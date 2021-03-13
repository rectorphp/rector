<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

final class SkipAssignExpressionUseVariable
{
    public function run()
    {
        $content = 1;
        $content = $content + 1;
        return $content;
    }
}
