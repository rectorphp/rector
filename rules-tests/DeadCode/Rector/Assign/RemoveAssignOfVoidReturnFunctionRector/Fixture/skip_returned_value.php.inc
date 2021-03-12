<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveAssignOfVoidReturnFunctionRector\Fixture;

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
