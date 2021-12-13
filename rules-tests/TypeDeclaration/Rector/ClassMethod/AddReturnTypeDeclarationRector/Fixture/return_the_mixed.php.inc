<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector\Fixture;

final class ReturnTheMixed
{
    public function create()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector\Fixture;

final class ReturnTheMixed
{
    public function create(): mixed
    {
    }
}

?>
