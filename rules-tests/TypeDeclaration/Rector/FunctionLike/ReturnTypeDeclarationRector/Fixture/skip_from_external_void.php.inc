<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\vendor\ExternalForVoid;

final class SkipFromExternalVoid extends ExternalForVoid
{
    public function run()
    {
    }
}
