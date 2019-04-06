<?php

namespace Rector\Php\Tests\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector\Fixture;

class Keep
{
    public function run($values)
    {
        foreach ($values as $value) {
            break;
        }

        switch ($values) {
            case 1:
                return 1;
                break;
        }

        foreach ($values as $value) {
            if ($value === 5) {
                break;
            }
        }
    }
}
