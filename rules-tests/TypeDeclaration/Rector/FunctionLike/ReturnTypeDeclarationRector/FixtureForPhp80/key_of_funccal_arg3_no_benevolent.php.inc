<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureForPhp80;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Exception\ShouldNotHappenException;

final class KeyOfFuncCallArg3NoBenevolent
{
    public function getKey(FuncCall $funcCall)
    {
        foreach ($funcCall->args as $position => $arg) {
            if (rand(0, 1)) {
                return $position;
            }

            if (rand(1, 2)) {
                return 'test';
            }
        }

        throw new ShouldNotHappenException();
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureForPhp80;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Exception\ShouldNotHappenException;

final class KeyOfFuncCallArg3NoBenevolent
{
    public function getKey(FuncCall $funcCall): int|string
    {
        foreach ($funcCall->args as $position => $arg) {
            if (rand(0, 1)) {
                return $position;
            }

            if (rand(1, 2)) {
                return 'test';
            }
        }

        throw new ShouldNotHappenException();
    }
}

?>
