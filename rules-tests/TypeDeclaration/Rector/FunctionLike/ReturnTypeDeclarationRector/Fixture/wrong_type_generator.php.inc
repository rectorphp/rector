<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

final class WrongTypeGenerator
{
    /**
     * @return \wrong
     **/
    public function generator()
    {
        yield 5;
    }
}
?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

final class WrongTypeGenerator
{
    /**
     * @return \wrong
     **/
    public function generator(): \Generator
    {
        yield 5;
    }
}
?>
