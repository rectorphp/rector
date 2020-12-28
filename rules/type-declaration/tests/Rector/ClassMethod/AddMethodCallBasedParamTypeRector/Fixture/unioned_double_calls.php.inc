<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Fixture;

class UnionedDoubleCalls
{
    public function getById($id)
    {
    }
}

class CallerClassUnionedDoubleCalls
{
    public function run()
    {
        $someClass = new UnionedDoubleCalls();
        $someClass->getById(5);
        $someClass->getById(null);
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\Fixture;

class UnionedDoubleCalls
{
    public function getById(?int $id)
    {
    }
}

class CallerClassUnionedDoubleCalls
{
    public function run()
    {
        $someClass = new UnionedDoubleCalls();
        $someClass->getById(5);
        $someClass->getById(null);
    }
}

?>
