<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Empty_;
final class AlwaysStrictBoolExprAnalyzer
{
    /**
     * @var array<class-string<Expr>>
     */
    private const BOOL_TYPE_NODES = [
        // detect strict type here :)
        Empty_::class,
        BooleanAnd::class,
        BooleanOr::class,
        Equal::class,
        NotEqual::class,
        Identical::class,
        NotIdentical::class,
    ];
    public function isStrictBoolExpr(Expr $expr) : bool
    {
        foreach (self::BOOL_TYPE_NODES as $boolTypeNode) {
            if (\is_a($expr, $boolTypeNode, \true)) {
                return \true;
            }
        }
        return $expr instanceof ConstFetch && \in_array($expr->name->toLowerString(), ['true', 'false'], \true);
    }
}
