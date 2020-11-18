<?php

namespace Rector\SOLID\Tests\Rector\Assign\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipUsageInMultipleLevels
{
    function myMethod()
    {
        $varused = 1;

        if (mktime() === false) {
            if (true) {
                if (false) {
                    return $varused;
                }
            }
        }

        echo $varused;
    }
}
