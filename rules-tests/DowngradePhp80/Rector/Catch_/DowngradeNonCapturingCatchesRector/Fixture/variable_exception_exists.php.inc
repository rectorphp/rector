<?php

namespace Rector\Tests\DowngradePhp80\Rector\Catch_\DowngradeNonCapturingCatchesRector\Fixture;

class VariableExceptionExists
{
    public function run()
    {
        $exception = 'test';

        try {
            throw new Exception('message');
        } catch (\Exception) {
        }

        echo $exception;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Catch_\DowngradeNonCapturingCatchesRector\Fixture;

class VariableExceptionExists
{
    public function run()
    {
        $exception = 'test';

        try {
            throw new Exception('message');
        } catch (\Exception $exception2) {
        }

        echo $exception;
    }
}

?>
