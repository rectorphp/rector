<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

final class SkipAssignExpressionUseVariableAsFunctionParam
{
    public function run()
    {
        $content = [1, 2, 3];
        $content = array_push($content, 1);
        return $content;
    }
}
