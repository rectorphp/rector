<?php

namespace Rector\Tests\DependencyInjection\Rector\Class_\MultiParentingToAbstractDependencyRector\Fixture;

use Rector\Tests\DependencyInjection\Rector\Class_\MultiParentingToAbstractDependencyRector\Source\FirstImplementer;
use Rector\Tests\DependencyInjection\Rector\Class_\MultiParentingToAbstractDependencyRector\Source\ImplementInterface;
use Rector\Tests\DependencyInjection\Rector\Class_\MultiParentingToAbstractDependencyRector\Source\SecondImplementer;

abstract class AbstractParentClassSkipDifferentImplementations
{
    /**
     * @var ImplementInterface
     */
    private $implement;

    public function __construct(ImplementInterface $implement)
    {
        $this->implement = $implement;
    }
}

class FirstChildDifferentImplementations extends AbstractParentClassSkipDifferentImplementations
{
    public function __construct(FirstImplementer $firstImplementer)
    {
        parent::__construct($firstImplementer);
    }
}

class SecondChildDifferentImplementations extends AbstractParentClassSkipDifferentImplementations
{
    public function __construct(SecondImplementer $secondImplementer)
    {
        parent::__construct($secondImplementer);
    }
}
