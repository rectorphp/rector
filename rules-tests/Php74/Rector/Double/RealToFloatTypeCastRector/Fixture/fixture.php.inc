<?php

namespace Rector\Tests\Php74\Rector\Double\RealToFloatTypeCastRector\Fixture;

class Fixture
{
    public function run()
    {
        $number = (real) 5;
        $number = (float) 5;
        $number = (double) 5;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Double\RealToFloatTypeCastRector\Fixture;

class Fixture
{
    public function run()
    {
        $number = (float) 5;
        $number = (float) 5;
        $number = (double) 5;
    }
}

?>
