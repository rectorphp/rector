<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictReturnExprRector\Fixture;

final class SomeClass
{
    public function run()
    {
        return $this->first() && true;
    }

    public function first()
    {
        return true;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictReturnExprRector\Fixture;

final class SomeClass
{
    public function run(): bool
    {
        return $this->first() && true;
    }

    public function first(): bool
    {
        return true;
    }
}

?>
