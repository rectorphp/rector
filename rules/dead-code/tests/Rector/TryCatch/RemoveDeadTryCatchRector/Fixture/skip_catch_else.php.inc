<?php

namespace Rector\DeadCode\Tests\Rector\TryCatch\RemoveDeadTryCatchRector\Fixture;

use InvalidArgumentException;

class SkipCatchElse
{
    public function run()
    {
        try {
            $one = 1;
        }
        catch (Throwable $throwable) {
            $throwable = new InvalidArgumentException();
            throw $throwable;
        }
    }
}
