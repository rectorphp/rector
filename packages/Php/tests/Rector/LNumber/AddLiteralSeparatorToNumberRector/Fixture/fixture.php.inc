<?php

namespace Rector\Php\Tests\Rector\LNumber\AddLiteralSeparatorToNumberRector\Fixture;

class SomeClass
{
    public function run()
    {
        $int = 1000;
        $float = 1000500.001;
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\LNumber\AddLiteralSeparatorToNumberRector\Fixture;

class SomeClass
{
    public function run()
    {
        $int = 1_000;
        $float = 1_000_500.001;
    }
}

?>
