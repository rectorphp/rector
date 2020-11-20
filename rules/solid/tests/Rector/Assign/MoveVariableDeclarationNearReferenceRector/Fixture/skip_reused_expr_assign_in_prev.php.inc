<?php

namespace Rector\SOLID\Tests\Rector\Assign\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipReusedExprAssignInPrev
{
    function myMethod($x)
    {
        $tag = $x;
        echo $x;

        $x = 'test';
        echo $x;

        echo $tag;
    }
}
