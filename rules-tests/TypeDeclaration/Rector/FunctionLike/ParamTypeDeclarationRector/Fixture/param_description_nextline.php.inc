<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

final class DemoFile
{
    /**
     * @param int $foo
     *   My description1
     * @param int $foo2 a
     *   My description2
     */
    public function run($foo, $foo2)
    {
        return $foo;
    }
}
?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

final class DemoFile
{
    /**
     * @param int $foo
     *   My description1
     * @param int $foo2 a
     *   My description2
     */
    public function run(int $foo, int $foo2)
    {
        return $foo;
    }
}
?>
