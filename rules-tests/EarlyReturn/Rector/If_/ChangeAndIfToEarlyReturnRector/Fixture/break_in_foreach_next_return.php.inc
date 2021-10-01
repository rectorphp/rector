<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class BreakInForeachNextReturn
{
    public function run(array $data, bool $a, bool $b)
    {
        foreach ($data as $value) {
            if ($a === $b && ! $value) {
                break;
            }

            return true;
        }

        return false;
    }
}

?>
-----
<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class BreakInForeachNextReturn
{
    public function run(array $data, bool $a, bool $b)
    {
        foreach ($data as $value) {
            if ($a !== $b) {
                return true;
            }
            if ($value) {
                return true;
            }
            break;
        }

        return false;
    }
}

?>
