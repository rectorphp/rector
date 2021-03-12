<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveAssignOfVoidReturnFunctionRector\Fixture;

class SkipReturnedValue
{
    public function run()
    {
        $value = $this->getOne();
    }

    private function getOne(): int
    {
        return 1;
    }
}
