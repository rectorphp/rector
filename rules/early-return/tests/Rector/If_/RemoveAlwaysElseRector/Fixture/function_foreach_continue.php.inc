<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

class FunctionForeachContinue
{
    public function run($list, $flag)
    {
        function ($list) use ($flag) {
            foreach ($list as $key => $val) {
                if ($flag && empty($key)) {
                    continue;
                } elseif (empty($val)) {
                    $val = $key;
                }
            }
        };
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\RemoveAlwaysElseRector\Fixture;

class FunctionForeachContinue
{
    public function run($list, $flag)
    {
        function ($list) use ($flag) {
            foreach ($list as $key => $val) {
                if ($flag && empty($key)) {
                    continue;
                }
                if (empty($val)) {
                    $val = $key;
                }
            }
        };
    }
}

?>
