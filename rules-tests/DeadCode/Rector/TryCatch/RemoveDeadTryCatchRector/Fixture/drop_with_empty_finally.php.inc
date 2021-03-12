<?php

namespace Rector\DeadCode\Tests\Rector\TryCatch\RemoveDeadTryCatchRector\Fixture;

class DropWithEmptyFinally
{
    public function run()
    {
        try {
            // some code
        } catch (\Throwable $throwable) {
            throw $throwable;
        } finally {
        }
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\TryCatch\RemoveDeadTryCatchRector\Fixture;

class DropWithEmptyFinally
{
    public function run()
    {
        // some code

    }
}

?>
