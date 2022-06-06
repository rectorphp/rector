<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Coalesce;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticPropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
final class CoalesceAnalyzer
{
    /**
     * @var array<class-string<Expr>>
     */
    private const ISSETABLE_EXPR = [Variable::class, ArrayDimFetch::class, PropertyFetch::class, StaticPropertyFetch::class];
    public function hasIssetableLeft(Coalesce $coalesce) : bool
    {
        $leftClass = \get_class($coalesce->left);
        return \in_array($leftClass, self::ISSETABLE_EXPR, \true);
    }
}
