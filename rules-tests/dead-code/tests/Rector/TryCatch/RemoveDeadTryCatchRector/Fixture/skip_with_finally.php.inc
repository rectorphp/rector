<?php

namespace Rector\DeadCode\Tests\Rector\TryCatch\RemoveDeadTryCatchRector\Fixture;

class SkipWithFinally
{
    public function run()
    {
        try {
            // some code
        } catch (\Throwable $throwable) {
            throw $throwable;
        } finally {
            $this->resetState();
        }
    }

    private function resetState()
    {
    }
}

