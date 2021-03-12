<?php

declare(strict_types=1);

namespace Rector\Tests\NetteCodeQuality\Rector\Assign\MakeGetComponentAssignAnnotatedRector\Source;

use Nette\Application\UI\Control;

final class ExternalControl extends Control
{
    public function createComponentAnother(): AnotherControl
    {
        return new AnotherControl();
    }
}
