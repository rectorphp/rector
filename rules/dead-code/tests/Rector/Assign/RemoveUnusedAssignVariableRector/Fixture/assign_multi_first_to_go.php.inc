<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

final class AssignMultiFirstToGo
{
    public function run()
    {
        $peValId = $values = 0;
        if ($values) {
            return true;
        }
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

final class AssignMultiFirstToGo
{
    public function run()
    {
        $values = 0;
        if ($values) {
            return true;
        }
    }
}

?>
