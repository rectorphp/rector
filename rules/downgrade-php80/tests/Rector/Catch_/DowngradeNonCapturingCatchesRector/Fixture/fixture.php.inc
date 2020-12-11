<?php

namespace Rector\DowngradePhp80\Tests\Rector\Catch_\DowngradeNonCapturingCatchesRector\Fixture;

class SomeClass
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

namespace Rector\DowngradePhp80\Tests\Rector\Catch_\DowngradeNonCapturingCatchesRector\Fixture;

class SomeClass
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
