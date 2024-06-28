<?php

declare (strict_types=1);
namespace Rector\PostRector\Contract\Rector;

use PhpParser\Node\Stmt;
use PhpParser\NodeVisitor;
/**
 * @internal
 */
interface PostRectorInterface extends NodeVisitor
{
    /**
     * @param Stmt[] $stmts
     */
    public function shouldTraverse(array $stmts) : bool;
}
