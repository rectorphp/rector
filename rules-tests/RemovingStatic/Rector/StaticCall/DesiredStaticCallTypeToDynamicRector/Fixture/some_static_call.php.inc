<?php

namespace Rector\Tests\RemovingStatic\Rector\StaticCall\DesiredStaticCallTypeToDynamicRector\Fixture;

use Rector\Tests\RemovingStatic\Rector\StaticCall\DesiredStaticCallTypeToDynamicRector\Source\SomeStaticMethod;

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

namespace Rector\Tests\RemovingStatic\Rector\StaticCall\DesiredStaticCallTypeToDynamicRector\Fixture;

use Rector\Tests\RemovingStatic\Rector\StaticCall\DesiredStaticCallTypeToDynamicRector\Source\SomeStaticMethod;

final class SomeStaticCall
{
    public function run()
    {
        $this->someStaticMethod->go();
    }
}

?>
