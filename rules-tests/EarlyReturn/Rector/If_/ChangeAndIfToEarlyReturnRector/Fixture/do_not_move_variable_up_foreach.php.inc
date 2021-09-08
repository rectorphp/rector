<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class DoNotMoveVariableUpForeach
{
    public function run(array $data, $a, $b)
    {
        foreach ($data as $value) {
            if ($a && $b) {
                unset($value);
            }
        }

        $targets = [];
        return $targets;
    }
}

?>
-----
<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class DoNotMoveVariableUpForeach
{
    public function run(array $data, $a, $b)
    {
        foreach ($data as $value) {
            if (!$a) {
                continue;
            }
            if (!$b) {
                continue;
            }
            unset($value);
        }

        $targets = [];
        return $targets;
    }
}

?>
