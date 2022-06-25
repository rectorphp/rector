<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictReturnExprRector\Fixture;

final class VoidReturnInInnerFunction
{
    public function run()
    {
        return $this->first() && true;
    }

    public function first()
    {
        function () {
            return;
        };

        return true;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictReturnExprRector\Fixture;

final class VoidReturnInInnerFunction
{
    public function run(): bool
    {
        return $this->first() && true;
    }

    public function first(): bool
    {
        function () {
            return;
        };

        return true;
    }
}

?>
