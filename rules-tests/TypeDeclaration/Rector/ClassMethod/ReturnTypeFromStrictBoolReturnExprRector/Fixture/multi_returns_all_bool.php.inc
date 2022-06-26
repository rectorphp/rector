<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictBoolReturnExprRector\Fixture;

final class MultiReturnsAllBool
{
    public function run(array $values)
    {
        foreach ($values as $value) {
            return true;
        }

        return false;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictBoolReturnExprRector\Fixture;

final class MultiReturnsAllBool
{
    public function run(array $values): bool
    {
        foreach ($values as $value) {
            return true;
        }

        return false;
    }
}

?>
