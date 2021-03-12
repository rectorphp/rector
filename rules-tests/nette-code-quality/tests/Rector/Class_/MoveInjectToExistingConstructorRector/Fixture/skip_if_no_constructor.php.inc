<?php

namespace Rector\NetteCodeQuality\Tests\Rector\Class_\MoveInjectToExistingConstructorRector\Fixture;

final class SkipIfNoConstructor
{
    /**
     * @var SomeDependency
     * @inject
     */
    public $someDependency;
}
