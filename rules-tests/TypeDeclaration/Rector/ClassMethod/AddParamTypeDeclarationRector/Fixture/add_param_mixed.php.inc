<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector\Contract\ParentTypeToMixed;

final class AddParamMixed implements ParentTypeToMixed
{
    public function process($name)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector\Contract\ParentTypeToMixed;

final class AddParamMixed implements ParentTypeToMixed
{
    public function process(mixed $name)
    {
    }
}

?>
