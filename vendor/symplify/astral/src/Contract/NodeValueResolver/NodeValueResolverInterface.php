<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Symplify\Astral\Contract\NodeValueResolver;

use PhpParser\Node\Expr;
/**
 * @template TExpr as Expr
 */
interface NodeValueResolverInterface
{
    /**
     * @return class-string<TExpr>
     */
    public function getType() : string;
    /**
     * @param TExpr $expr
     * @return mixed
     */
    public function resolve(\PhpParser\Node\Expr $expr, string $currentFilePath);
}
