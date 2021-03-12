<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\SimplifyConditionsRector\Fixture;

class SkipDualNull
{
    public function run()
    {
        if (($totalVariance !== null) === ($numFeatures !== null)) {
            return true;
        }
    }
}
