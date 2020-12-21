<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveDuplicatedIfReturnRector\Fixture;

class Fixture
{
    public function run($value)
    {
        if ($value) {
            return true;
        }

        $value2 = 100;

        if ($value) {
            return true;
        }
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveDuplicatedIfReturnRector\Fixture;

class Fixture
{
    public function run($value)
    {
        if ($value) {
            return true;
        }

        $value2 = 100;
    }
}

?>
