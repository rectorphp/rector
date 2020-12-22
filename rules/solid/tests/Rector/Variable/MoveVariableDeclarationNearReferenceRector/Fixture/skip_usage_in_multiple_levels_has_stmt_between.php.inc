<?php

namespace Rector\SOLID\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipUsageInMultipleLevelsHasStmtBetween
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

        echo 'STATEMENT HERE';

        echo $varused;
    }
}
?>
