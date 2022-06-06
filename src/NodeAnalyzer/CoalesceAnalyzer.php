<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
final class CoalesceAnalyzer
{
    /**
     * @var array<class-string<Expr>>
     */
    private const ISSETABLE_EXPR = [\PhpParser\Node\Expr\Variable::class, \PhpParser\Node\Expr\ArrayDimFetch::class, \PhpParser\Node\Expr\PropertyFetch::class, \PhpParser\Node\Expr\StaticPropertyFetch::class];
    public function hasIssetableLeft(\PhpParser\Node\Expr\BinaryOp\Coalesce $coalesce) : bool
    {
        $leftClass = \get_class($coalesce->left);
        return \in_array($leftClass, self::ISSETABLE_EXPR, \true);
    }
}
