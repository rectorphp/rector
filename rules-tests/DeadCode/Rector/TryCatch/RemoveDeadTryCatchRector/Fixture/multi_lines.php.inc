<?php

namespace Rector\DeadCode\Tests\Rector\TryCatch\RemoveDeadTryCatchRector\Fixture;

class MultiLines
{
    public function run()
    {
        try {
            $one = 1;
            $two = 1;
            if ($one + $two > 1) {
                return 155;
            }
        }
        catch (Throwable $throwable) {
            throw $throwable;
        }
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\TryCatch\RemoveDeadTryCatchRector\Fixture;

class MultiLines
{
    public function run()
    {
        $one = 1;
        $two = 1;
        if ($one + $two > 1) {
            return 155;
        }
    }
}

?>
