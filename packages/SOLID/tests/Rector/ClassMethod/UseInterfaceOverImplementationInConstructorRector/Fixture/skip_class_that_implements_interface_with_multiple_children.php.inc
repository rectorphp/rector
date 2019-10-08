<?php

namespace Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Fixture;

use Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Source\Orange;

class SkipClassThatImplementsInterfaceWithMultipleChildren
{
    public function __construct(Orange $someImplementation)
    {
    }
}
