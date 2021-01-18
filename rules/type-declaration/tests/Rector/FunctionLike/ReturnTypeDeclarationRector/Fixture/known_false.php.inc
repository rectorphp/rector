<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

final class KnownFalse
{
    /**
     * @return false
     */
    public function getFloating($value)
    {
        return $value;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

final class KnownFalse
{
    /**
     * @return false
     */
    public function getFloating($value): bool
    {
        return $value;
    }
}

?>
