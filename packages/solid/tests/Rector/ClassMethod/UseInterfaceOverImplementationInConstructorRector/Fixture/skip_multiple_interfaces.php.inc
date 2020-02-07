<?php

namespace Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Fixture;

use Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Source\ClassThatImplementsMoreInterfaces;

class SkipMultipleInterfaces
{
    public function __construct(ClassThatImplementsMoreInterfaces $someImplementation)
    {
    }
}
