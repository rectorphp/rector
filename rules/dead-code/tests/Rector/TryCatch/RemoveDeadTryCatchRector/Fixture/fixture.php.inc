<?php

namespace Rector\DeadCode\Tests\Rector\TryCatch\RemoveDeadTryCatchRector\Fixture;

class Fixture
{
    public function run()
    {
        try {
            // some code
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

class Fixture
{
    public function run()
    {
        // some code

    }
}

?>
