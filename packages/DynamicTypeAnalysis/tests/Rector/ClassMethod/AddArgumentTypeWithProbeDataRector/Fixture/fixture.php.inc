<?php

namespace Rector\DynamicTypeAnalysis\Tests\Rector\ClassMethod\AddArgumentTypeWithProbeDataRector\Fixture;

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

namespace Rector\DynamicTypeAnalysis\Tests\Rector\ClassMethod\AddArgumentTypeWithProbeDataRector\Fixture;

class SomeClass
{
    public function run(string $arg)
    {
        return $arg;
    }
}

?>
