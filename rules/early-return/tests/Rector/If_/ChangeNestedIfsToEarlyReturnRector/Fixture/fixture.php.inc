<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeNestedIfsToEarlyReturnRector\Fixture;

class Fixture
{
    public function run()
    {
        if ($value === 5) {
            if ($value2 === 10) {
                return 'yes';
            }
        }

        return 'no';
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeNestedIfsToEarlyReturnRector\Fixture;

class Fixture
{
    public function run()
    {
        if ($value !== 5) {
            return 'no';
        }
        if ($value2 === 10) {
            return 'yes';
        }

        return 'no';
    }
}

?>
