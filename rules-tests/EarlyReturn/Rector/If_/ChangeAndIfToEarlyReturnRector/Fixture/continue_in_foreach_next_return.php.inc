<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class ContinueInForeachNextReturn
{
    public function run(array $data, bool $a, bool $b)
    {
        foreach ($data as $value) {
            if ($a === $b && ! $value) {
                continue;
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

class ContinueInForeachNextReturn
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
            continue;
        }

        return false;
    }
}

?>
