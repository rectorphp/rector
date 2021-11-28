<?php

declare (strict_types=1);
namespace Rector\ReadWrite\Contract;

use PhpParser\Node\Expr;
/**
 * @template TExpr as Expr
 */
interface ReadNodeAnalyzerInterface
{
    /**
     * @param \PhpParser\Node\Expr $expr
     */
    public function supports($expr) : bool;
    /**
     * @param \PhpParser\Node\Expr $expr
     */
    public function isRead($expr) : bool;
}
