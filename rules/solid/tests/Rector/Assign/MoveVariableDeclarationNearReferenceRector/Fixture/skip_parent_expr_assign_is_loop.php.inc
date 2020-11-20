<?php

namespace Rector\SOLID\Tests\Rector\Assign\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipParentExprAssignIsLoop
{
    function myMethod(array $data, $x)
    {
        $c = count($data);

        foreach ($data as $value) {
            $x .= $c;
        }
    }
}
