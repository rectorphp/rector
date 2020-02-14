<?php

namespace Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Fixture;

class SomeClass
{
    public function __construct(\Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Fixture\SomeImplementation $someImplementation)
    {
    }
}

class SomeImplementation implements SomeInterface
{
}

interface SomeInterface
{
}

?>
-----
<?php

namespace Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Fixture;

class SomeClass
{
    public function __construct(\Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Fixture\SomeInterface $someImplementation)
    {
    }
}

class SomeImplementation implements SomeInterface
{
}

interface SomeInterface
{
}

?>
