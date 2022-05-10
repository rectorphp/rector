<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector\Fixture;

final class SkipWithEmptyIf
{
    public function run()
    {
        $newNode = 'something';
        if ($newNode !== null) {
        }

        return 5;
    }
}
