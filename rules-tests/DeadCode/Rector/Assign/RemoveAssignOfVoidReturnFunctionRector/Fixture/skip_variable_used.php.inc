<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveAssignOfVoidReturnFunctionRector\Fixture;

$value = 1;

class VariableUsedOutsideMethod
{
    public function run()
    {
        $value = $this->getOne();
    }

    private function getOne(): void
    {
    }
}

echo $value

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveAssignOfVoidReturnFunctionRector\Fixture;

$value = 1;

class VariableUsedOutsideMethod
{
    public function run()
    {
        $this->getOne();
    }

    private function getOne(): void
    {
    }
}

echo $value

?>