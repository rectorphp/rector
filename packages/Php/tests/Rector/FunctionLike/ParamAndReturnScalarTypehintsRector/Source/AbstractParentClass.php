<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnScalarTypehintsRector\Source;

abstract class AbstractParentClass
{
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }
}
