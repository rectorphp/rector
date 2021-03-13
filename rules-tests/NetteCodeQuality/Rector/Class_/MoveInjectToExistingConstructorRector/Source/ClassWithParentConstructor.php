<?php

declare(strict_types=1);

namespace Rector\Tests\NetteCodeQuality\Rector\Class_\MoveInjectToExistingConstructorRector\Source;

abstract class ClassWithParentConstructor
{
    public function __construct()
    {
        $yes = 'no';
    }
}
