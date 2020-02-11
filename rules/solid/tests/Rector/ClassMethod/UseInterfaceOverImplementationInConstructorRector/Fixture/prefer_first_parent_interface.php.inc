<?php

namespace Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Fixture;

use Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Source\ClassThatImplementsInterfaceThatExtendsInterface;

class PreferFirstParentInterface
{
    public function __construct(ClassThatImplementsInterfaceThatExtendsInterface $someImplementation)
    {
    }
}

?>
-----
<?php

namespace Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Fixture;

use Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Source\ClassThatImplementsInterfaceThatExtendsInterface;

class PreferFirstParentInterface
{
    public function __construct(\Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Source\InterfaceExtendingInterface $someImplementation)
    {
    }
}

?>
