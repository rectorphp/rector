<?php

namespace Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Fixture;

use Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Source\ClassThatImplementsInterfaceAndUsesTraitWithAdditionalMethods;

class SkipClassThatImplementsSingleInterfaceButUsesTraitWithAdditionalMethods
{
    public function __construct(ClassThatImplementsInterfaceAndUsesTraitWithAdditionalMethods $someImplementation)
    {
    }
}
