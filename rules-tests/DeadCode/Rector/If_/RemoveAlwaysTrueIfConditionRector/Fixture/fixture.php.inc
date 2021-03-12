<?php

namespace Rector\DeadCode\Tests\Rector\If_\RemoveAlwaysTrueIfConditionRector\Fixture;

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

namespace Rector\DeadCode\Tests\Rector\If_\RemoveAlwaysTrueIfConditionRector\Fixture;

class Fixture
{
    public function run()
    {
        return 'hi';

        return 'hello';
    }
}

?>
