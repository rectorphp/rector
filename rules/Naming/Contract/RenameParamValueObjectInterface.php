<?php

declare (strict_types=1);
namespace Rector\Naming\Contract;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
interface RenameParamValueObjectInterface extends \Rector\Naming\Contract\RenameValueObjectInterface
{
    /**
     * @return \PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_
     */
    public function getFunctionLike();
    public function getParam() : \PhpParser\Node\Param;
}
