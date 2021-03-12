<?php

namespace Rector\Tests\NetteCodeQuality\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Fixture;

use Rector\Tests\NetteCodeQuality\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\AnotherControl;
use Rector\Tests\NetteCodeQuality\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\ExternalControl;

final class SkipNonExistingMethod
{
    public function run()
    {
        $externalControl = new ExternalControl();
        $anotherControl = $externalControl->getComponent('missing');
    }
}
