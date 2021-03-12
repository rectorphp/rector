<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

class AssignNewInstance
{
    public function run()
    {
        $days = new \stdClass();
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

class AssignNewInstance
{
    public function run()
    {
    }
}

?>
