<?php

namespace Rector\Php\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

use Rector\Php\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Source\SniffInterface;

final class CoolSniff implements SniffInterface
{
    /**
     * @param int $position
     */
    public function process(string $file, $position)
    {
    }
}
