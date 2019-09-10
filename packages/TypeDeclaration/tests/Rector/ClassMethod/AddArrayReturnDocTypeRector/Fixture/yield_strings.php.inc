<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use stdClass;

class YieldsStrings
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

use stdClass;

class YieldsStrings
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
