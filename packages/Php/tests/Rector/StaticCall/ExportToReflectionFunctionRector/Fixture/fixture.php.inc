<?php

namespace Rector\Php\Tests\Rector\StaticCall\ExportToReflectionFunctionRector\Fixture;

class ReflectableFunction
{
    public function run()
    {
        $reflectionFunction = \ReflectionFunction::export('foo');
        $reflectionFunctionAsString = \ReflectionFunction::export('foo', true);
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\StaticCall\ExportToReflectionFunctionRector\Fixture;

class ReflectableFunction
{
    public function run()
    {
        $reflectionFunction = new \ReflectionFunction('foo');
        $reflectionFunctionAsString = (string) new \ReflectionFunction('foo');
    }
}

?>
