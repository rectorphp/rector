<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

final class SkipParamDoc
{
    /**
     * @param false $isActive
     */
    public function setId($isActive = false)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

final class SkipParamDoc
{
    public function setId(bool $isActive = false)
    {
    }
}

?>
