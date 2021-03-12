<?php

namespace Rector\RemovingStatic\Tests\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector\Fixture;

class DoubleStaticMethod
{
    public static function run()
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

namespace Rector\RemovingStatic\Tests\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector\Fixture;

class DoubleStaticMethod
{
    public function run()
    {
        $this->someStatic();
    }

    private function someStatic()
    {
    }
}

?>
