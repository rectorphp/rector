<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class OnlyWrittenLocalVariable
{
    public function run()
    {
        $fromDate = null;
        if (isset($params['todate'])) {
            $fromDate = 123;
            $z = true;
        }

        if (isset($params['abc'])) {
            $fromDate = 456;
        }

        if ($z) {
            echo "yes";
        }
    }
}

?>
    -----
<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

class OnlyWrittenLocalVariable
{
    public function run()
    {
        if (isset($params['todate'])) {
            $z = true;
        }

        if ($z) {
            echo "yes";
        }
    }
}

?>
