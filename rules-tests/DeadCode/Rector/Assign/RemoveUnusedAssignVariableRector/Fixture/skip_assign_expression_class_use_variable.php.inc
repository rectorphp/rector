<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

final class SkipAssignExpressionClassUseVariable
{
    public function run()
    {
        $datetime = time();
        $datetime = new \DateTime($datetime);
        return $datetime;
    }
}
