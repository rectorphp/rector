<?php

namespace Rector\RemovingStatic\Tests\Rector\Class_\SingleStaticServiceToDynamicRector\Fixture;

class SomeClass
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

namespace Rector\RemovingStatic\Tests\Rector\Class_\SingleStaticServiceToDynamicRector\Fixture;

class SomeClass
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
