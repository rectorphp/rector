<?php

namespace Rector\Tests\CodeQualityStrict\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

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
?>
