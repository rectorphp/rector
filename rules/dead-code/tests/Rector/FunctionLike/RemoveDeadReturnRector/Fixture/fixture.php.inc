<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveDeadReturnRector\Fixture;

class Fixture
{
    public function run()
    {
        $shallWeDoThis = true;

        if ($shallWeDoThis) {
            return;
        }

        return;
    }

    public function anonymous()
    {
        $value = function() {
            if (true) {
                return 1 + 5;
            }

            return;
        };
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveDeadReturnRector\Fixture;

class Fixture
{
    public function run()
    {
        $shallWeDoThis = true;

        if ($shallWeDoThis) {
            return;
        }
    }

    public function anonymous()
    {
        $value = function() {
            if (true) {
                return 1 + 5;
            }
        };
    }
}

?>
