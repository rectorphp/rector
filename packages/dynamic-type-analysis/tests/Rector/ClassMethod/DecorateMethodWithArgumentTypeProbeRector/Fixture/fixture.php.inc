<?php

namespace Rector\DynamicTypeAnalysis\Tests\Rector\ClassMethod\DecorateMethodWithArgumentTypeProbeRector\Fixture;

class SomeClass
{
    public function run($arg)
    {
        return $arg;
    }
}

?>
-----
<?php

namespace Rector\DynamicTypeAnalysis\Tests\Rector\ClassMethod\DecorateMethodWithArgumentTypeProbeRector\Fixture;

class SomeClass
{
    public function run($arg)
    {
        \Rector\DynamicTypeAnalysis\Probe\TypeStaticProbe::recordArgumentType($arg, __METHOD__, 0);
        return $arg;
    }
}

?>
