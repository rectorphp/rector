<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class SkipFromExternalVoid extends PhpFileLoader
{
    public function import(
        $resource,
        string $type = null,
        $ignoreErrors = false,
        string $sourceResource = null,
        $exclude = null
    )
    {
    }
}