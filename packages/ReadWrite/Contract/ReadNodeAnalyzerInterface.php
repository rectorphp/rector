<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\ReadWrite\Contract;

use RectorPrefix20220606\PhpParser\Node\Expr;
/**
 * @template TExpr as Expr
 */
interface ReadNodeAnalyzerInterface
{
    public function supports(Expr $expr) : bool;
    /**
     * @param TExpr $expr
     */
    public function isRead(Expr $expr) : bool;
}
