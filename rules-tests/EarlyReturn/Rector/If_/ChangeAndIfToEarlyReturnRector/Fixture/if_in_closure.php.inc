<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class IfInClosure
{
    public function run()
    {
        function ($a, $b) {
            if ($a && $b) {
                return true;
            }
            return false;
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\EarlyReturn\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class IfInClosure
{
    public function run()
    {
        function ($a, $b) {
            if (!$a) {
                return false;
            }
            if (!$b) {
                return false;
            }
            return true;
        };
    }
}

?>
