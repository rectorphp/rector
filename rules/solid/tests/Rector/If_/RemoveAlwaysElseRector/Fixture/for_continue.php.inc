<?php

namespace Rector\SOLID\Tests\Rector\If_\RemoveAlwaysElse\Fixture;

class ForContinue
{
    public function run($values)
    {
        foreach ($values as $value) {
            if ($value) {
                continue;
            } else {
                echo '111';
            }
        }
    }

    public function run2($values)
    {
        for ($i = 0; count($values) < $i; $i++) {
            $value = $values[$i];

            if ($value) {
                continue;
            } else {
                echo '111';
            }
        }
    }

    public function run3($list, $flag)
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

namespace Rector\SOLID\Tests\Rector\If_\RemoveAlwaysElse\Fixture;

class ForContinue
{
    public function run($values)
    {
        foreach ($values as $value) {
            if ($value) {
                continue;
            }
            echo '111';
        }
    }

    public function run2($values)
    {
        for ($i = 0; count($values) < $i; $i++) {
            $value = $values[$i];

            if ($value) {
                continue;
            }
            echo '111';
        }
    }

    public function run3($list, $flag)
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
