<?php

namespace Rector\Doctrine\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Source\Apple;

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
        $building = new Apple();
        $someClass = new SecondPosition();
        $someClass->process('hi', $building->getId());
    }
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Source\Apple;

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
        $building = new Apple();
        $someClass = new SecondPosition();
        $someClass->process('hi', $building->getId());
    }
}

?>
