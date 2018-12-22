<?php

namespace Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnTypeDeclarationRector\Fixture;

use Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnTypeDeclarationRector\Source\AbstractParentClass;

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

namespace Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnTypeDeclarationRector\Fixture;

use Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnTypeDeclarationRector\Source\AbstractParentClass;

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
    public function change(int $number)
    {
    }
}

?>
