<?php

namespace Rector\Tests\DowngradePhp80\Rector\Catch_\DowngradeNonCapturingCatchesRector\Fixture;

class SkipAlready
{
    public function run()
    {
        try {
            // code
        } catch (\Exception $exception) {
            // error
        }
    }
}
