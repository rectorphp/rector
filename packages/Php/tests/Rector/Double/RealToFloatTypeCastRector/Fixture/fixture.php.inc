<?php

namespace Rector\Php\Tests\Rector\Double\RealToFloatTypeCastRector\Fixture;

class SomeClass
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

namespace Rector\Php\Tests\Rector\Double\RealToFloatTypeCastRector\Fixture;

class SomeClass
{
    public function run()
    {
        $number = (float) 5;
        $number = (float) 5;
        $number = (double) 5;
    }
}

?>
