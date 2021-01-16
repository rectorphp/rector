<?php

namespace Rector\RemovingStatic\Tests\Rector\StaticCall\DesiredStaticCallToDynamicRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\StaticCall\DesiredStaticCallToDynamicRector\Source\SomeStaticMethod;

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

namespace Rector\RemovingStatic\Tests\Rector\StaticCall\DesiredStaticCallToDynamicRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\StaticCall\DesiredStaticCallToDynamicRector\Source\SomeStaticMethod;

final class SomeStaticCall
{
    public function run()
    {
        $this->someStaticMethod->go();
    }
}

?>
