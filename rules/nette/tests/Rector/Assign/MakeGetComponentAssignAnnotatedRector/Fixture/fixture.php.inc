<?php

namespace Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Fixture;

use Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\ExternalControl;

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

namespace Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Fixture;

use Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\ExternalControl;

final class SomeClass
{
    public function run()
    {
        $externalControl = new ExternalControl();
        /** @var \Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\AnotherControl $anotherControl */
        $anotherControl = $externalControl->getComponent('another');
    }
}

?>
