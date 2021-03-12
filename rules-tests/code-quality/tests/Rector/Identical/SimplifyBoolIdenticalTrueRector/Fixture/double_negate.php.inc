<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\SimplifyBoolIdenticalTrueRector\Fixture;

class DoubleNegate
{
    public function run()
    {
        if (! true === false) {
            return 'yes';
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Identical\SimplifyBoolIdenticalTrueRector\Fixture;

class DoubleNegate
{
    public function run()
    {
        if (true) {
            return 'yes';
        }
    }
}

?>
