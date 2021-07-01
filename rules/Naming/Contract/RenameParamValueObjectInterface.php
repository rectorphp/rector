<?php

declare(strict_types=1);

namespace Rector\Naming\Contract;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;

interface RenameParamValueObjectInterface extends RenameValueObjectInterface
{
    public function getFunctionLike(): ClassMethod | Function_ | Closure;

    public function getParam(): Param;
}
