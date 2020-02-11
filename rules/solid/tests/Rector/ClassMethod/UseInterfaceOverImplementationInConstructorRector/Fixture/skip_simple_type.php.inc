<?php

namespace Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Fixture;

class SkipSimpleType
{
    public function __construct(int $someImplementation)
    {
    }
}
