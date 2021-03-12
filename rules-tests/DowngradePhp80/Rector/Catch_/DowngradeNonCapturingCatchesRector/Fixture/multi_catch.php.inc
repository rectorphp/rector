<?php

namespace Rector\DowngradePhp80\Tests\Rector\Catch_\DowngradeNonCapturingCatchesRector\Fixture;

class MultiCatch
{
    public function run()
    {
        try {
            // code
        } catch (\Exception|\RuntimeException) {
            // error
        }
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp80\Tests\Rector\Catch_\DowngradeNonCapturingCatchesRector\Fixture;

class MultiCatch
{
    public function run()
    {
        try {
            // code
        } catch (\Exception|\RuntimeException $exception) {
            // error
        }
    }
}

?>
