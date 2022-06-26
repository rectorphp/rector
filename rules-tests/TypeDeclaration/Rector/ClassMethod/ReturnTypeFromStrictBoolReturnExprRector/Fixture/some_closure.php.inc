<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictBoolReturnExprRector\Fixture;

final class SomeClosure
{
    public function run()
    {
        return function () {
            return 100 && true;
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictBoolReturnExprRector\Fixture;

final class SomeClosure
{
    public function run()
    {
        return function (): bool {
            return 100 && true;
        };
    }
}

?>
