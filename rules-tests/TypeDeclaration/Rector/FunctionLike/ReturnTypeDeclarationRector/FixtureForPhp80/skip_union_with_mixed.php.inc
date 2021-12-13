<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureForPhp80;

final class SkipUnionWithMixed
{
    /**
     * @return int|mixed
     */
    public function return($value)
    {
        return $value;
    }
}
