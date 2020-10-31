<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Fixture;

class RemoveReturnType
{
    public function clear(): array
    {
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Fixture;

class RemoveReturnType
{
    public function clear()
    {
    }
}

?>
