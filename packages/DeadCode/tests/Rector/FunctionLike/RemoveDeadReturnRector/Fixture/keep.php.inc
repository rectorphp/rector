<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveDeadReturnRector\Fixture;

class Keep
{
    public function run()
    {
        $shallWeDoThis = true;

        if ($shallWeDoThis) {
            return 1;
        }

        return 5;
    }

    public function stop()
    {
        $shallWeDoThis = true;

        if ($shallWeDoThis) {
            return 1;
        }
    }
}

function someFunctionWithNoReturn()
{
    return 1;
}
