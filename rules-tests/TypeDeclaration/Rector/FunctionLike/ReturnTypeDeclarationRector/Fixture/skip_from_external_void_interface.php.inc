<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\ExternalForVoidInterface;

final class SkipFromExternalVoidInterface implements ExternalForVoidInterface
{
    public function run()
    {
    }
}