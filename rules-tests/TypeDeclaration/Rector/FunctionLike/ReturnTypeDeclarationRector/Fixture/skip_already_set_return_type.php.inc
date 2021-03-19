<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;

abstract class ParentReturnType
{
    public function run(): ?\PhpParser\Node
    {
    }
}

final class SkipAlreadySetReturnType extends ParentReturnType
{
    public function run(): ?\PhpParser\Node
    {
        return new FuncCall(new Name('some'));
    }
}
