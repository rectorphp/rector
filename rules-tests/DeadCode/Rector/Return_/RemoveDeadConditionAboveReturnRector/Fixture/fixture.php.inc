<?php

namespace Rector\Tests\DeadCode\Rector\Return_\RemoveDeadConditionAboveReturnRector\Fixture;

class Fixture
{
    public function run()
    {
        if (1 === 1) {
            return 'yes';
        }

        return 'yes';
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Return_\RemoveDeadConditionAboveReturnRector\Fixture;

class Fixture
{
    public function run()
    {
        return 'yes';
    }
}

?>
