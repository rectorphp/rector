<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

final class MethodCallOnly
{
    public function cleanFiles()
    {
        $value = $this->processSomething();
    }

    public function processSomething()
    {
        return 1000;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

final class MethodCallOnly
{
    public function cleanFiles()
    {
        $this->processSomething();
    }

    public function processSomething()
    {
        return 1000;
    }
}

?>
