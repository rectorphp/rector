<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Source\vendor\AbstractParentClass;

final class ChildClass extends AbstractParentClass
{
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }

    /**
     * @param int $number
     */
    public function change($number)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Source\vendor\AbstractParentClass;

final class ChildClass extends AbstractParentClass
{
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }

    public function change(int $number)
    {
    }
}

?>
