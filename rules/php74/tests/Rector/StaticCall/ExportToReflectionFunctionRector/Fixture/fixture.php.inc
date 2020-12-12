<?php

namespace Rector\Php74\Tests\Rector\StaticCall\ExportToReflectionFunctionRector\Fixture;

class Fixture
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

namespace Rector\Php74\Tests\Rector\StaticCall\ExportToReflectionFunctionRector\Fixture;

class Fixture
{
    public function run()
    {
        $reflectionFunction = new \ReflectionFunction('foo');
        $reflectionFunctionAsString = (string) new \ReflectionFunction('foo');
    }
}

?>
