<?php

namespace Rector\SOLID\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipUsedInCatch
{
    function myMethod()
    {
        $position = getPosition();

        try {
            execute();
            echo $position;
        } catch (\Exception $e) {
            echo $position  + 1;
        } finally {
            echo $position  + 2;
        }
    }
}

?>
