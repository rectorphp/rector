<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use Symfony\Component\Console\Input\StringInput;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

final class SkipMagicCaller
{
    public function run(): array
    {
        $privatesCaller = new PrivatesCaller();
        return $privatesCaller->callPrivateMethod(new StringInput(''), 'tokenize', []);
    }
}
