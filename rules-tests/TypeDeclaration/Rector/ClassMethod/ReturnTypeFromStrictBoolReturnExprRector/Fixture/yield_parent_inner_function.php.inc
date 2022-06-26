<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictBoolReturnExprRector\Fixture;

final class YieldParentInnerFunction
{
    public function run()
    {
        return $this->first() && true;
    }

    public function first()
    {
        function () {
            yield 1;
        };

        return true;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictBoolReturnExprRector\Fixture;

final class YieldParentInnerFunction
{
    public function run(): bool
    {
        return $this->first() && true;
    }

    public function first(): bool
    {
        function () {
            yield 1;
        };

        return true;
    }
}

?>
