<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Source\vendor;

abstract class AbstractParentClass
{
    /**
     * @param int $number
     */
    public function keep($number)
    {
    }
}
