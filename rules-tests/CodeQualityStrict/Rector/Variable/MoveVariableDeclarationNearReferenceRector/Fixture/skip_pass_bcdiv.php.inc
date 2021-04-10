<?php

namespace Rector\Tests\CodeQualityStrict\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

final class SkipPassBcdiv
{
    public function run($data, $options)
    {
        $a = bcscale(3);
        echo bcdiv('105', '6.55957');
        echo $a;
    }
}