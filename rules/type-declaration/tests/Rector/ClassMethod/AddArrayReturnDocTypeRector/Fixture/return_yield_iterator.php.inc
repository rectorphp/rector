<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class ReturnYieldIterator
{
    public function someMethod()
    {
        yield ['test', 'test2'];
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

final class ReturnYieldIterator
{
    /**
     * @return \Iterator<string[]>
     */
    public function someMethod()
    {
        yield ['test', 'test2'];
    }
}

?>
