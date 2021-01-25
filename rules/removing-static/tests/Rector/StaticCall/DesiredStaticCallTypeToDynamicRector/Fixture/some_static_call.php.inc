<?php

namespace Rector\RemovingStatic\Tests\Rector\StaticCall\DesiredStaticCallTypeToDynamicRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\StaticCall\DesiredStaticCallTypeToDynamicRector\Source\SomeStaticMethod;

final class SomeStaticCall
{
    public function run()
    {
        SomeStaticMethod::go();
    }
}

?>
-----
<?php

namespace Rector\RemovingStatic\Tests\Rector\StaticCall\DesiredStaticCallTypeToDynamicRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\StaticCall\DesiredStaticCallTypeToDynamicRector\Source\SomeStaticMethod;

final class SomeStaticCall
{
    public function run()
    {
        $this->someStaticMethod->go();
    }
}

?>
