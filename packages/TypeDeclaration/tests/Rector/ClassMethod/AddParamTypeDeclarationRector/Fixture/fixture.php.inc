<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\ParentInterfaceWithChangeType;

class SomeClass implements ParentInterfaceWithChangeType
{
    public function process($name)
    {
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\ParentInterfaceWithChangeType;

class SomeClass implements ParentInterfaceWithChangeType
{
    public function process(string $name)
    {
    }
}

?>
