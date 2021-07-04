<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\vendor\ExternalForVoidTrait;

final class SkipFromExternalVoidTrait
{
    use ExternalForVoidTrait;

    public function run()
    {
    }
}
