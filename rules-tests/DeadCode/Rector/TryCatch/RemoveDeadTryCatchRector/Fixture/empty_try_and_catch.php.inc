<?php

namespace Rector\Tests\DeadCode\Rector\TryCatch\RemoveDeadTryCatchRector\Fixture;

class EmptyTryAndCatch
{
    public function run()
    {
        try {
            // some code
        }
        catch (Throwable $throwable) {
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\TryCatch\RemoveDeadTryCatchRector\Fixture;

class EmptyTryAndCatch
{
    public function run()
    {
    }
}

?>
