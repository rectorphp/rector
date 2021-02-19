<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Fixture;

final class SomeClass
{
    public function action()
    {
        return new Response();
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\Fixture;

final class SomeClass
{
    public function action(): Respose
    {
        return new Response();
    }
}

?>
