<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

abstract class AbstractClass
{
    /**
     * @param mixed[] $param
     * @return bool
     */
    public function getValue(array $param)
    {
        return rand(0, 1)
            ? true
            : false;
    }
}

class SkipChildReturnMixed extends AbstractClass
{
    /**
     * @param mixed[] $param
     * @return bool
     */
    public function getValue(array $param)
    {
        if (isset($param['data'])) {
            return $param['data'];
        }

        return false;
    }
}

?>
