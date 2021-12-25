<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\FixtureUnionType;

final class ArrayAndScalarType
{
    /**
     * @param scalar|scalar[] $id
     */
    public function run($id)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\FixtureUnionType;

final class ArrayAndScalarType
{
    /**
     * @param scalar|scalar[] $id
     */
    public function run(array|bool|float|int|string $id)
    {
    }
}

?>
