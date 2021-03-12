<?php

namespace Rector\RemovingStatic\Tests\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector\Source\SomeParent;

class CaseWithParent extends SomeParent
{
    private static function someStatic()
    {
    }

    public function run()
    {
        self::someStatic();
    }
}

?>
-----
<?php

namespace Rector\RemovingStatic\Tests\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector\Source\SomeParent;

class CaseWithParent extends SomeParent
{
    private function someStatic()
    {
    }

    public function run()
    {
        $this->someStatic();
    }
}

?>
