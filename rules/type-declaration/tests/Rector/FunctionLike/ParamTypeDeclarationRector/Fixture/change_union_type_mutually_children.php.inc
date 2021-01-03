<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class ChangeUnionTypeMutuallyChildren
{
    /**
     * @param CDataChild|CTypeParent $pointer
     */
    public static function sizeof(&$pointer): int
    {
    }
}

class CDataChild extends CTypeParent
{
}

class CTypeParent
{
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class ChangeUnionTypeMutuallyChildren
{
    /**
     * @param CDataChild|CTypeParent $pointer
     */
    public static function sizeof(\Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\CDataChild &$pointer): int
    {
    }
}

class CDataChild extends CTypeParent
{
}

class CTypeParent
{
}

?>
