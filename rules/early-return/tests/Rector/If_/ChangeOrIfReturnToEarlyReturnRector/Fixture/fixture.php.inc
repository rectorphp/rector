<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeOrIfReturnToEarlyReturnRector\Fixture;

class Fixture
{
    public function run($a, $b, $c, $d)
    {
        // a comment
        if ($a || $b || $c || $d) {
            return null;
        }
        return 'another';
    }
}

?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeOrIfReturnToEarlyReturnRector\Fixture;

class Fixture
{
    public function run($a, $b, $c, $d)
    {
        // a comment
        if ($a) {
            return null;
        }
        if ($b) {
            return null;
        }
        if ($c) {
            return null;
        }
        if ($d) {
            return null;
        }
        return 'another';
    }
}

?>
