<?php

namespace Rector\SOLID\Tests\Rector\Assign\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipAlreadyInCondition
{
    function myMethod()
    {
        if (rand(0,1) === 1) {
            $content = 'x';
        }

        echo $content . ' test';
    }
}
