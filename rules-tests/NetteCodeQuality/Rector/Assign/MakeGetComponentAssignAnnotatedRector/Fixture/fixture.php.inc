<?php

namespace Rector\Tests\NetteCodeQuality\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Fixture;

use Rector\Tests\NetteCodeQuality\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\ExternalControl;

final class SomeClass
{
    public function run()
    {
        $externalControl = new ExternalControl();
        $anotherControl = $externalControl->getComponent('another');
    }
}

?>
-----
<?php

namespace Rector\Tests\NetteCodeQuality\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Fixture;

use Rector\Tests\NetteCodeQuality\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\ExternalControl;

final class SomeClass
{
    public function run()
    {
        $externalControl = new ExternalControl();
        /** @var \Rector\Tests\NetteCodeQuality\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\AnotherControl $anotherControl */
        $anotherControl = $externalControl->getComponent('another');
    }
}

?>
