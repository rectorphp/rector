<?php

namespace Rector\CodingStyle\Tests\Rector\Identical\IdenticalFalseToBooleanNotRector\Fixture;

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

namespace Rector\CodingStyle\Tests\Rector\Identical\IdenticalFalseToBooleanNotRector\Fixture;

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
