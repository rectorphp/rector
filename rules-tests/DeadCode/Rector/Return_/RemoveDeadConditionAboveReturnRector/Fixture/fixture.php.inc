<?php

namespace Rector\DeadCode\Tests\Rector\Return_\RemoveDeadConditionAboveReturnRector\Fixture;

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

namespace Rector\DeadCode\Tests\Rector\Return_\RemoveDeadConditionAboveReturnRector\Fixture;

class Fixture
{
    public function run()
    {
        return 'yes';
    }
}

?>
