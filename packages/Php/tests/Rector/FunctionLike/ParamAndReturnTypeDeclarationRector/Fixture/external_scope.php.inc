<?php

namespace Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnTypeDeclarationRector\Fixture;

use Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnTypeDeclarationRector\Source\SniffInterface;

final class CoolSniff implements SniffInterface
{
    /**
     * @param int $position
     */
    public function process(string $file, $position)
    {
    }
}
