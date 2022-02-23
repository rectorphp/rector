<?php

namespace Rector\Tests\DeadCode\Rector\TryCatch\RemoveDeadTryCatchRector\Fixture;

class SkipMultiLinesDifferentThrowVariable
{
    public function run($variable)
    {
        try {
            $this->doSomething();
            $this->doSomethingElse();
        }
        catch (Throwable $throwable) {
            throw $variable;
        }
    }
}
