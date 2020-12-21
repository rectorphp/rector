<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

class AssignStringLong
{
    public function run()
    {
        $removeMe = <<<EOF
Value: {$embed}<br />
EOF;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

class AssignStringLong
{
    public function run()
    {
    }
}

?>
