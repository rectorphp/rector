<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

class AssignScalar
{
    public function run()
    {
        $days = [1 => 'one', 'two'];
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

class AssignScalar
{
    public function run()
    {
    }
}

?>
