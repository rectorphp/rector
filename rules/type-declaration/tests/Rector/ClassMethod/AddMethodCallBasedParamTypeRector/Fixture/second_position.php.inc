<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Fixture;

use Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Source\Coconut;

class SecondPosition
{
    public function process($name, $id)
    {
    }
}

class CallerClassForSecondPosition
{
    public function run()
    {
        $building = new Coconut();
        $someClass = new SecondPosition();
        $someClass->process('hi', $building->getId());
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Fixture;

use Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Source\Coconut;

class SecondPosition
{
    public function process(string $name, \Ramsey\Uuid\UuidInterface $id)
    {
    }
}

class CallerClassForSecondPosition
{
    public function run()
    {
        $building = new Coconut();
        $someClass = new SecondPosition();
        $someClass->process('hi', $building->getId());
    }
}

?>
