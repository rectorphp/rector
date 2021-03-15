<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

class AssignProperty
{
    private $days = '123';

    public function run()
    {
        $days = $this->days;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

class AssignProperty
{
    private $days = '123';

    public function run()
    {
    }
}

?>
