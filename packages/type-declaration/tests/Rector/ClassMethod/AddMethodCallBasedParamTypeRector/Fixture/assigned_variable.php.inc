<?php

namespace Rector\Doctrine\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Source\Apple;

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
        $building = new Apple();
        $someClass = new AssignedVariable();

        $assignedVariable = $building->getId();
        $someClass->getById($assignedVariable);
    }
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Source\Apple;

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
        $building = new Apple();
        $someClass = new AssignedVariable();

        $assignedVariable = $building->getId();
        $someClass->getById($assignedVariable);
    }
}

?>
