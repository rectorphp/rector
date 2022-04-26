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
    public function run(bool|string|int|float|array $id)
    {
    }
}

?>
