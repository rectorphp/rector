<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureForPhp80;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Exception\ShouldNotHappenException;

final class SkipKeyOfFuncCallArgHasReturnType
{
    public function getKey(FuncCall $funcCall, Arg $desiredArg): int
    {
        foreach ($funcCall->args as $position => $arg) {
            if ($arg !== $desiredArg) {
                continue;
            }

            return $position;
        }

        throw new ShouldNotHappenException();
    }
}
