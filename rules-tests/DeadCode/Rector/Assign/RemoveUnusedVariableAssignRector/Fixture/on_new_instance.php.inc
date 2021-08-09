<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

use Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Source\HeavyClass;
use RuntimeException;

class OnNewInstance
{
    public function run()
    {
        try {
            $obj = new HeavyClass();
        } catch (RuntimeException $r) {
            throw $r;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

use Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Source\HeavyClass;
use RuntimeException;

class OnNewInstance
{
    public function run()
    {
        try {
            new HeavyClass();
        } catch (RuntimeException $r) {
            throw $r;
        }
    }
}

?>
