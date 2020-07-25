<?php

namespace Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Fixture;

use Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\AnotherControl;
use Rector\Nette\Tests\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source\ExternalControl;

final class SkipAlreadyDoc
{
    public function run()
    {
        $externalControl = new ExternalControl();
        /** @var AnotherControl $anotherControl */
        $anotherControl = $externalControl->getComponent('another');
    }
}
