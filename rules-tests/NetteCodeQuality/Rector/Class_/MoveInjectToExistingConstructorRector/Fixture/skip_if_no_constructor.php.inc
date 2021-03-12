<?php

namespace Rector\Tests\NetteCodeQuality\Rector\Class_\MoveInjectToExistingConstructorRector\Fixture;

final class SkipIfNoConstructor
{
    /**
     * @var SomeDependency
     * @inject
     */
    public $someDependency;
}
