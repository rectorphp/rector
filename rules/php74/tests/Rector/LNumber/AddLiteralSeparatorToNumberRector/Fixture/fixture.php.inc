<?php

namespace Rector\Php74\Tests\Rector\LNumber\AddLiteralSeparatorToNumberRector\Fixture;

class Fixture
{
    public function run()
    {
        $int = 1000;
        $int2 = 1000000;
        $float = 1000.0;
        $float2 = 1000000.0;
        $float3 = 1000500.001;
    }
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\LNumber\AddLiteralSeparatorToNumberRector\Fixture;

class Fixture
{
    public function run()
    {
        $int = 1000;
        $int2 = 1_000_000;
        $float = 1000.0;
        $float2 = 1_000_000.0;
        $float3 = 1_000_500.001;
    }
}

?>
