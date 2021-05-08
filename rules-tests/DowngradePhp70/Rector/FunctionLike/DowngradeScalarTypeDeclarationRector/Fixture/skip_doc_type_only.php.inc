<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector\Fixture;

final class SkipDocTypeOnly
{
    /**
     * @param string $id
     */
    public function hasDefinition($id)
    {
        return $id;
    }
}
