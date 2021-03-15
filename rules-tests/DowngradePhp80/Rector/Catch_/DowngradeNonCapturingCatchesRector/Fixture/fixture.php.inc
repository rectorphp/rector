<?php

namespace Rector\Tests\DowngradePhp80\Rector\Catch_\DowngradeNonCapturingCatchesRector\Fixture;

class Fixture
{
    public function run()
    {
        try {
            // code
        } catch (\Exception) {
            // error
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Catch_\DowngradeNonCapturingCatchesRector\Fixture;

class Fixture
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

?>
