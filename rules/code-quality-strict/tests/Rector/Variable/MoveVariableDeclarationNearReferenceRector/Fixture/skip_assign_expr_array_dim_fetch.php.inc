<?php

namespace Rector\CodeQualityStrict\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

final class SkipAssignExprArrayDimFetch
{
    public function run()
    {
        $value = $values[$i];
        ++$i;
        return $value;
    }
}