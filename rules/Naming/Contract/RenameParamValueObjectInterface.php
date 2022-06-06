<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Naming\Contract;

use RectorPrefix20220606\PhpParser\Node\Expr\Closure;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Function_;
interface RenameParamValueObjectInterface extends RenameValueObjectInterface
{
    /**
     * @return \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure
     */
    public function getFunctionLike();
    public function getParam() : Param;
}
