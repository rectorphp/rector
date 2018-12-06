<?php

namespace Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnScalarTypehintsRector\Wrong;

final class LocalChildClass extends AbstractLocalParentClass
{
    /**
     * @param int $number
     */
    public function change($number)
    {
    }

    /**
     * @param int $number
     */
    public function changeToo($number)
    {
    }
}

abstract class AbstractLocalParentClass
{
    /**
     * @param int $number
     */
    public function changeToo($number)
    {
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnScalarTypehintsRector\Wrong;

final class LocalChildClass extends AbstractLocalParentClass
{
    /**
     * @param int $number
     */
    public function change(int $number)
    {
    }

    /**
     * @param int $number
     */
    public function changeToo(int $number)
    {
    }
}

abstract class AbstractLocalParentClass
{
    /**
     * @param int $number
     */
    public function changeToo(int $number)
    {
    }
}

?>
