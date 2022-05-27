<?php

declare (strict_types=1);
namespace Rector\ReadWrite\Contract;

use PhpParser\Node\Expr;
/**
 * @template TExpr as Expr
 */
interface ReadNodeAnalyzerInterface
{
    public function supports(\PhpParser\Node\Expr $expr) : bool;
    /**
     * @param TExpr $expr
     */
    public function isRead(\PhpParser\Node\Expr $expr) : bool;
}
