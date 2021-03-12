<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector\Fixture;

class Fixture
{
    public function run()
    {
        if (true === true) {
            return 'hi';
        }

        return 'hello';
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector\Fixture;

class Fixture
{
    public function run()
    {
        return 'hi';

        return 'hello';
    }
}

?>
