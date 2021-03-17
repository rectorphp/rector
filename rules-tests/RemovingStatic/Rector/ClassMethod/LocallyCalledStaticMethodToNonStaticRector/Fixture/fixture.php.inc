<?php

namespace Rector\Tests\RemovingStatic\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector\Fixture;

class Fixture
{
    public function run()
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

class Fixture
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
