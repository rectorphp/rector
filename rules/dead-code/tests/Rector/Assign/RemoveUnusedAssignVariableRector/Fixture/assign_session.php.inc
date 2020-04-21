<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

class AssignSession
{
    public function run()
    {
        $days = $_SESSION['ID'];
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

class AssignSession
{
    public function run()
    {
    }
}

?>
