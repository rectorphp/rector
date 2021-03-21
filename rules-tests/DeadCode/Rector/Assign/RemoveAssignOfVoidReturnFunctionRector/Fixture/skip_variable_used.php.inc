<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveAssignOfVoidReturnFunctionRector\Fixture;

class SkipVariableUsed
{
    public function run()
    {
        $value = $this->getOne();
        echo $value;
    }

    private function getOne(): void
    {
    }
}

?>