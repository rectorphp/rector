<?php

namespace Rector\Tests\CodeQuality\Rector\Return_\SimplifyUselessVariableRector\Fixture;

function SkipClosureUseByReference()
{
    $isChanged = false;
    (function ($x) use (&$isChanged) {
        $isChanged = true;
        return $isChanged;
    })('x');

    if ($isChanged) {
        return true;
    }

    return false;
}