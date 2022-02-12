<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

final class DemoFile
{
    /**
     * @return \Generator<int>
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

final class DemoFile
{
    /**
     * @return \Generator<int>
     **/
    public function generator(): \Generator
    {
        yield 5;
    }
}
?>
