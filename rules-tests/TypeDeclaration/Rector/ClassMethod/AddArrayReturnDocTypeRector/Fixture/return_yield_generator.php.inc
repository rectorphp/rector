<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class ReturnYieldGenerator
{
    public function someMethod()
    {
        yield ['test', 'test2'];
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class ReturnYieldGenerator
{
    /**
     * @return \Generator<string[]>
     */
    public function someMethod()
    {
        yield ['test', 'test2'];
    }
}

?>
