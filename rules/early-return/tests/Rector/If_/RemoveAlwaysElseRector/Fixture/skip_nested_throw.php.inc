<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

use Throwable;

class SkipNestedThrow
{
    public function run($value)
    {
        if ($value) {
            if ($value !== 5) {
                throw new \InvalidStateException;
            }
        } else {
            return 10;
        }
    }

    public function run2($value)
    {
        if ($value) {
            try {
                if ($value !== 5) {
                    throw new \InvalidStateException;
                }
            } catch (Throwable $throwable) {
            }
        } else {
            return 10;
        }
    }
}
