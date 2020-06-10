<?php

namespace Rector\CodeQuality\Tests\Rector\Array_\ArrayThisCallToThisMethodCallRector\Fixture;

class SkipNonExistingMethod
{
    public function run()
    {
        $values = [1, 5, 3];
        usort($values, [$this, 'compareSizeThat']);
    }
}
