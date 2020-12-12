<?php

namespace Rector\SOLID\Tests\Rector\If_\ChangeNestedIfsToEarlyReturnRector\Fixture;

class HavingElseifs
{
    public function run()
    {
        if ($value === 5) {
            if ($value2 === 10) {
                return 'yes';
            } elseif ($value === 555) {
                return 'maybe';
            }
        }

        return 'no';
    }
}

?>
-----
<?php

namespace Rector\SOLID\Tests\Rector\If_\ChangeNestedIfsToEarlyReturnRector\Fixture;

class HavingElseifs
{
    public function run()
    {
        if ($value !== 5) {
            return 'no';
        }
        if ($value2 === 10) {
            return 'yes';
        } elseif ($value === 555) {
            return 'maybe';
        }

        return 'no';
    }
}

?>
