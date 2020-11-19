<?php

namespace Rector\SOLID\Tests\Rector\Assign\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipUsageInsideLoop
{
    function myMethod($x)
    {
        for ($i = 0; $i < 10; $i++) {
            if ($x) {
                $i = 10;
            }
        }
    }
}
