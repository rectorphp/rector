<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromCallersRector\Fixture;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;

final class SkipFunctionLike
{
    /**
     * @param ClassMethod|Function_ $functionLike
     */
    public function run(FunctionLike $functionLike)
    {
        $this->print($functionLike);
    }

    public function print(\PhpParser\Node\FunctionLike $functionLike)
    {
    }
}
