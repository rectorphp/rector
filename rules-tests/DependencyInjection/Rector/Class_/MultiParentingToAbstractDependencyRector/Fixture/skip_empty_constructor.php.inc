<?php

namespace Rector\Tests\DependencyInjection\Rector\Class_\MultiParentingToAbstractDependencyRector\Fixture;

use Rector\Tests\DependencyInjection\Rector\Class_\MultiParentingToAbstractDependencyRector\Source\SomeDependency;

abstract class AbstractParentClassSkipEmptyConstructor
{
    /**
     * @var SomeDependency
     */
    private $someDependency;

    public function __construct(SomeDependency $someDependency)
    {
        $this->someDependency = $someDependency;
    }
}

class FirstChildSkipEmptyConstructor extends AbstractParentClassSkipEmptyConstructor
{
    public function __construct()
    {
        $value = 5;
    }
}

class SecondChildSkipEmptyConstructor extends AbstractParentClassSkipEmptyConstructor
{
    public function __construct()
    {
        $value = 15;
    }
}
