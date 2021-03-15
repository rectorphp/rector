<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector\Fixture;

class Fixture
{
    public function run($var)
    {
        $var = null;
        if (rand(0,1)) {
            $var = 1 ;
        }

        if (rand(0,2)) {
            $var = 2 ;
        }

        return $var;
    }
}

?>
-----
<?php

namespace Rector\Tests\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector\Fixture;

class Fixture
{
    public function run($var)
    {
        if (rand(0,1)) {
            return 1;
        }

        if (rand(0,2)) {
            return 2;
        }

        return null;
    }
}

?>
