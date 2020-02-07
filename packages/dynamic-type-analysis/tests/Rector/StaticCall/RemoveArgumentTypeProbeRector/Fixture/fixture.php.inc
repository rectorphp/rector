<?php

namespace Rector\DynamicTypeAnalysis\Tests\Rector\StaticCall\RemoveArgumentTypeProbeRector\Fixture;

use Rector\DynamicTypeAnalysis\Probe\TypeStaticProbe;

class SomeClass
{
    public function run($arg)
    {
        TypeStaticProbe::recordArgumentType($arg, __METHOD__, 0);
    }
}

?>
-----
<?php

namespace Rector\DynamicTypeAnalysis\Tests\Rector\StaticCall\RemoveArgumentTypeProbeRector\Fixture;

use Rector\DynamicTypeAnalysis\Probe\TypeStaticProbe;

class SomeClass
{
    public function run($arg)
    {
    }
}

?>
