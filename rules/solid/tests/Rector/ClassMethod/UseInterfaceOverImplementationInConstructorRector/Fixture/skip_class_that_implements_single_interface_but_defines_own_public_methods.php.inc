<?php

namespace Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Fixture;

use Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Source\ClassThatImplementsInterfaceAndDefinesOwnPublicMethods;

class SkipClassThatImplementsSingleInterfaceButDefinesOwnPublicMethods
{
    public function __construct(ClassThatImplementsInterfaceAndDefinesOwnPublicMethods $someImplementation)
    {
    }
}
