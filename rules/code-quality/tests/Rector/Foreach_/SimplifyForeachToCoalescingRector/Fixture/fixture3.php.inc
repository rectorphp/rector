<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\SimplifyForeachToCoalescingRector\Fixture;

class Fixture3
{
    public function run()
    {
        $newValue = null;
        $values = [];
        $input = '123';

        foreach ($values as $key => $value) {
            if ($key === $input) {
                $newValue = $value;
            }
        }

        foreach ($values as $key => $value) {
            if ($input === $key) {
                $newValue = $value;
            }
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\SimplifyForeachToCoalescingRector\Fixture;

class Fixture3
{
    public function run()
    {
        $newValue = null;
        $values = [];
        $input = '123';

        $newValue = $values[$input] ?? $newValue;

        $newValue = $values[$input] ?? $newValue;
    }
}

?>
