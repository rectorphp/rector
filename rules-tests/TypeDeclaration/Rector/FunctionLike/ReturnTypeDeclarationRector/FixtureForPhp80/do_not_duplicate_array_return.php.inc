<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureForPhp80;

final class DoNotDuplicateArrayReturn
{
    /**
     * @return bool|mixed[]|float|string[]
     */
    public function go($number)
    {
        return execute();
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureForPhp80;

final class DoNotDuplicateArrayReturn
{
    /**
     * @return bool|mixed[]|float|string[]
     */
    public function go($number): bool|array|float
    {
        return execute();
    }
}

?>
