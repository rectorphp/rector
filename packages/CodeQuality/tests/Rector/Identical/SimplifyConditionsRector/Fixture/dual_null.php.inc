<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\SimplifyConditionsRector\Fixture;

class DualNull
{
    public function run()
    {
        if (($totalVariance !== null) === ($numFeatures !== null)) {
            return true;
        }
    }
}
