<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;

final class SkipClosure
{
    /**
     * @var ClassMethod|Function_|Closure
     */
    private $functionLike;

    /**
     * @return ClassMethod|Function_|Closure
     */
    public function getFunctionLike(): FunctionLike
    {
        return $this->functionLike;
    }
}
