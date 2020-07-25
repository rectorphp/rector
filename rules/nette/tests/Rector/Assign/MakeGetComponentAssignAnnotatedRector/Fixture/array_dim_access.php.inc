<?php

namespace Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Fixture;

use Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\ExternalControl;

final class ArrayDimAccess
{
    public function run()
    {
        $externalControl = new ExternalControl();
        $anotherControl = $externalControl['another'];
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Fixture;

use Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\ExternalControl;

final class ArrayDimAccess
{
    public function run()
    {
        $externalControl = new ExternalControl();
        /** @var \Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\AnotherControl $anotherControl */
        $anotherControl = $externalControl['another'];
    }
}

?>
