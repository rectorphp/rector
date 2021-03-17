<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

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

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class YieldStrings
{
    /**
     * @return \Iterator<string>
     */
    public function getValues(): iterable
    {
        yield 'tom';
        yield 'tam';
    }
}

?>
