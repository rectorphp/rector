<?php

namespace Rector\Tests\RemovingStatic\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector\Fixture;

final class DoubleStaticMethod
{
    private static function run()
    {
        self::someStatic();
    }

    private static function someStatic()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\RemovingStatic\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector\Fixture;

final class DoubleStaticMethod
{
    private function run()
    {
        $this->someStatic();
    }

    private function someStatic()
    {
    }
}

?>
