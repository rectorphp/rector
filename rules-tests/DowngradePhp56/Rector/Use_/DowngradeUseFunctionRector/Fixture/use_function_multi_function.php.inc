<?php

namespace Rector\Tests\DowngradePhp56\Rector\Use_\DowngradeUseFunctionRector\Fixture;

use function Foo\Bar\baz, Qux\quux;

final class SomeClass
{
    public function run()
    {
        return strtolower(baz() . quux());
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp56\Rector\Use_\DowngradeUseFunctionRector\Fixture;

final class SomeClass
{
    public function run()
    {
        return strtolower(\Foo\Bar\baz() . \Qux\quux());
    }
}

?>
