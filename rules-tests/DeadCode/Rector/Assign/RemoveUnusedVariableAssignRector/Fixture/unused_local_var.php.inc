<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class UnusedLocalVar
{
    public function run()
    {
        $fromDate = null;
        $test = date('Y-m-d');
        echo $test;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class UnusedLocalVar
{
    public function run()
    {
        $test = date('Y-m-d');
        echo $test;
    }
}

?>
