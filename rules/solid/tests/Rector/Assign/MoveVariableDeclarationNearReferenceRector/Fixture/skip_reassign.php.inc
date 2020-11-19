<?php

namespace Rector\SOLID\Tests\Rector\Assign\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipReAssign
{
    function myMethod($x)
    {
        $tag = 'A';

        $oldTag = $tag;
        $tag = 'B';

        if ($x) {
            return $oldTag;
        }

        return $tag;
    }
}
