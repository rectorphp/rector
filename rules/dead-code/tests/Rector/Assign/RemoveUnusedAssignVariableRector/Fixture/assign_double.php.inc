<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

class AssignDouble
{
    public function run()
    {
        $days = [1 => 'one', 'two'];

        $days = ['something_else'];
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

class AssignDouble
{
    public function run()
    {
    }
}

?>
