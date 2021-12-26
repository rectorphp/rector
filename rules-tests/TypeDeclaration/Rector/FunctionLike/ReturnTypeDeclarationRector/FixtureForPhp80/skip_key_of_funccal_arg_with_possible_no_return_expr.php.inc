<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureForPhp80;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Exception\ShouldNotHappenException;

final class SkipKeyOfFuncCallArgWithPossibleNoReturnExpr
{
    public function getKey(FuncCall $funcCall, Arg $desiredArg)
    {
        foreach ($funcCall->args as $position => $arg) {
            if (rand(0, 1)) {
                return $position;
            }

            if (rand(1, 2)) {
                return;
            }
        }

        throw new ShouldNotHappenException();
    }
}
