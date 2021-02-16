<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector\Fixture;

final class SomeClass
{
    public function getValues()
    {
        $value = 1000;
        return;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector\Fixture;

final class SomeClass
{
    public function getValues(): void
    {
        $value = 1000;
        return;
    }
}

?>
