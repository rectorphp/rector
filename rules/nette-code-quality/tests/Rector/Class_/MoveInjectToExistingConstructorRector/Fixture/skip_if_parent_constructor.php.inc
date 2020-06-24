<?php

namespace Rector\NetteCodeQuality\Tests\Rector\Class_\MoveInjectToExistingConstructorRector\Fixture;

use Rector\NetteCodeQuality\Tests\Rector\Class_\MoveInjectToExistingConstructorRector\Source\ClassWithParentConstructor;

final class SkipIfParentConstructor extends ClassWithParentConstructor
{
    /**
     * @var SomeDependency
     * @inject
     */
    public $someDependency;
}
