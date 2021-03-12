<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

final class FalseTrueUnionType
{
    /**
     * @return false|true
     */
    public function getTruth()
    {
        return (bool) mt_rand(0, 1);
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

final class FalseTrueUnionType
{
    /**
     * @return false|true
     */
    public function getTruth(): bool
    {
        return (bool) mt_rand(0, 1);
    }
}

?>
