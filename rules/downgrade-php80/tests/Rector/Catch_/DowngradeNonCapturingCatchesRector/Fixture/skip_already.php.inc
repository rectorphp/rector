<?php

namespace Rector\DowngradePhp80\Tests\Rector\Catch_\DowngradeNonCapturingCatchesRector\Fixture;

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
