<?php

namespace Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnScalarTypehintsRector\Wrong;

use Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnScalarTypehintsRector\Source\AbstractParentClass;

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

namespace Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnScalarTypehintsRector\Wrong;

use Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnScalarTypehintsRector\Source\AbstractParentClass;

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
