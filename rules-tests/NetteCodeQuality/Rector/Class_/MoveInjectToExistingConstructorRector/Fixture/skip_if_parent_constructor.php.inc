<?php

namespace Rector\Tests\NetteCodeQuality\Rector\Class_\MoveInjectToExistingConstructorRector\Fixture;

use Rector\Tests\NetteCodeQuality\Rector\Class_\MoveInjectToExistingConstructorRector\Source\ClassWithParentConstructor;

final class SkipIfParentConstructor extends ClassWithParentConstructor
{
    /**
     * @var SomeDependency
     * @inject
     */
    public $someDependency;
}
