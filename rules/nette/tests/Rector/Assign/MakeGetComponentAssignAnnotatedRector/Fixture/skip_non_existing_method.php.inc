<?php

namespace Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Fixture;

use Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\AnotherControl;
use Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\ExternalControl;

final class SkipNonExistingMethod
{
    public function run()
    {
        $externalControl = new ExternalControl();
        $anotherControl = $externalControl->getComponent('missing');
    }
}
