<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector\Fixture;

final class YieldInsideInnerFunction
{
    public function getValues()
    {
        $result = function () {
            yield [1000];
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector\Fixture;

final class YieldInsideInnerFunction
{
    public function getValues(): void
    {
        $result = function () {
            yield [1000];
        };
    }
}

?>
