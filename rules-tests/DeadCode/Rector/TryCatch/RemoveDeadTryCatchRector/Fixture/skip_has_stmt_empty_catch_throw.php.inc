<?php

namespace Rector\Tests\DeadCode\Rector\TryCatch\RemoveDeadTryCatchRector\Fixture;

class SkipHasStmtEmptyCatchThrow
{
    public function run()
    {
        try {
            $this->call();
        }
        catch (Throwable $throwable) {
        }
    }
}
