<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class YieldStrings
{
    public function getValues(): iterable
    {
        yield 'tom';
        yield 'tam';
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class YieldStrings
{
    /**
     * @return string[]
     */
    public function getValues(): iterable
    {
        yield 'tom';
        yield 'tam';
    }
}

?>
