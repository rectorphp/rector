<?php

declare(strict_types=1);

namespace Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Source;

final class ClassThatImplementsInterfaceAndUsesTraitWithAdditionalMethods implements InterfaceFive
{
    use TraitOne;
}
