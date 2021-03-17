<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Continue_;

final class SkipReturnStmt
{
    public function run(Continue_ $continue): Stmt
    {
        if (mt_rand(0, 1)) {
            return new Break_();
        }

        return $continue;
    }
}
