<?php

namespace Rector\Tests\DeadCode\Rector\Return_\RemoveDeadConditionAboveReturnRector\Fixture;

class DifferentCond
{
    public function run()
    {
        if (true) {
            return 'yes';
        }

        return 'yes';
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Return_\RemoveDeadConditionAboveReturnRector\Fixture;

class DifferentCond
{
    public function run()
    {
        return 'yes';
    }
}

?>
