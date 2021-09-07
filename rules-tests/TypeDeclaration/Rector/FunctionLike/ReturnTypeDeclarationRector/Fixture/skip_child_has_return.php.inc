<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

abstract class AbstractClass2
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

class SkipChildHasReturn1 extends AbstractClass2
{
    /**
     * @param mixed[] $param
     * @return bool
     */
    public function getValue(array $param): bool
    {
        return is_array($param);
    }
}

class SkipChildHasReturn2 extends AbstractClass2
{
    /**
     * @param mixed[] $param
     * @return string
     */
    public function getValue(array $param): string
    {
        return 'test';
    }
}

?>
