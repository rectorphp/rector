<?php

namespace Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Fixture;

use Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Source\InterfaceExtendingInterface;

class SkipInterface
{
    public function __construct(InterfaceExtendingInterface $someImplementation)
    {
    }
}
