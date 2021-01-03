<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

class SkipUnionType
{
    /**
     * Returns size of C data type of the given FFI\CData or FFI\CType.
     *
     * @param CData|CType $pointer
     */
    public static function sizeof(&$pointer): int
    {
    }
}

class CData
{
}

class CType
{
}
