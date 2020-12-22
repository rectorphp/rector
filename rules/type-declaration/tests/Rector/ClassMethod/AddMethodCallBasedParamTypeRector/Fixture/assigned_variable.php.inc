<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Fixture;

use Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Source\Coconut;

class AssignedVariable
{
    public function getById($id)
    {
    }
}

class CallerClassAssignedVariable
{
    public function run()
    {
        $coconut = new Coconut();
        $someClass = new AssignedVariable();

        $assignedVariable = $coconut->getId();
        $someClass->getById($assignedVariable);
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Fixture;

use Rector\SOLID\Tests\Rector\ClassMethod\UseInterfaceOverImplementationInConstructorRector\Source\Coconut;

class AssignedVariable
{
    public function getById(\Ramsey\Uuid\UuidInterface $id)
    {
    }
}

class CallerClassAssignedVariable
{
    public function run()
    {
        $coconut = new Coconut();
        $someClass = new AssignedVariable();

        $assignedVariable = $coconut->getId();
        $someClass->getById($assignedVariable);
    }
}

?>
