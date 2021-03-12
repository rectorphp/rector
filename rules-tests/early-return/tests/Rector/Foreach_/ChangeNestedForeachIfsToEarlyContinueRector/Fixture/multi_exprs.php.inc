<?php

namespace Rector\EarlyReturn\Tests\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector\Fixture;

class MultiExprs
{
    public function run()
    {
        $items = [];

        foreach ($values as $value) {
            if ($value === 5) {
                if ($value2 === 10) {
                    $items[] = 'maybe';
                    $items[] = 'no';
                    $items[] = 'yes';
                }
            }
        }
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector\Fixture;

class MultiExprs
{
    public function run()
    {
        $items = [];

        foreach ($values as $value) {
            if ($value !== 5) {
                continue;
            }
            if ($value2 !== 10) {
                continue;
            }
            $items[] = 'maybe';
            $items[] = 'no';
            $items[] = 'yes';
        }
    }
}

?>
