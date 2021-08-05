<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Source\vendor\SniffInterface;

final class SkipVendorLocatedTypes implements SniffInterface
{
    /**
     * @param int $position
     */
    public function process(string $file, $position)
    {
    }
}
