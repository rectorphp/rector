<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Source\SniffInterface;

final class CoolSniff implements SniffInterface
{
    /**
     * @param int $position
     */
    public function process(string $file, $position)
    {
    }
}
