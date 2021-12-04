<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\LogicalAnd;
use PhpParser\Node\Expr\BinaryOp\LogicalOr;
use PhpParser\Node\Expr\BitwiseNot;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\UnaryPlus;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PostRector\Collector\NodesToAddCollector;
final class LivingCodeManipulator
{
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\Rector\PostRector\Collector\NodesToAddCollector $nodesToAddCollector, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodesToAddCollector = $nodesToAddCollector;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function addLivingCodeBeforeNode(\PhpParser\Node\Expr $expr, \PhpParser\Node $addBeforeThisNode) : void
    {
        $livinExprs = $this->keepLivingCodeFromExpr($expr);
        foreach ($livinExprs as $livinExpr) {
            $this->nodesToAddCollector->addNodeBeforeNode(new \PhpParser\Node\Stmt\Expression($livinExpr), $addBeforeThisNode);
        }
    }
    /**
     * @return Expr[]|mixed[]
     * @param int|\PhpParser\Node|string|null $expr
     */
    public function keepLivingCodeFromExpr($expr) : array
    {
        if (!$expr instanceof \PhpParser\Node\Expr) {
            return [];
        }
        if ($expr instanceof \PhpParser\Node\Expr\Closure || $expr instanceof \PhpParser\Node\Scalar || $expr instanceof \PhpParser\Node\Expr\ConstFetch) {
            return [];
        }
        if ($this->isNestedExpr($expr)) {
            return $this->keepLivingCodeFromExpr($expr->expr);
        }
        if ($expr instanceof \PhpParser\Node\Expr\Variable) {
            return $this->keepLivingCodeFromExpr($expr->name);
        }
        if ($expr instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return \array_merge($this->keepLivingCodeFromExpr($expr->var), $this->keepLivingCodeFromExpr($expr->name));
        }
        if ($expr instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            $type = $this->nodeTypeResolver->getType($expr->var);
            if ($type instanceof \PHPStan\Type\ObjectType) {
                $objectType = new \PHPStan\Type\ObjectType('ArrayAccess');
                if ($objectType->isSuperTypeOf($type)->yes()) {
                    return [$expr];
                }
            }
            return \array_merge($this->keepLivingCodeFromExpr($expr->var), $this->keepLivingCodeFromExpr($expr->dim));
        }
        if ($expr instanceof \PhpParser\Node\Expr\ClassConstFetch || $expr instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
            return \array_merge($this->keepLivingCodeFromExpr($expr->class), $this->keepLivingCodeFromExpr($expr->name));
        }
        if ($this->isBinaryOpWithoutChange($expr)) {
            /** @var BinaryOp $binaryOp */
            $binaryOp = $expr;
            return $this->processBinary($binaryOp);
        }
        if ($expr instanceof \PhpParser\Node\Expr\Instanceof_) {
            return \array_merge($this->keepLivingCodeFromExpr($expr->expr), $this->keepLivingCodeFromExpr($expr->class));
        }
        if ($expr instanceof \PhpParser\Node\Expr\Isset_) {
            return $this->processIsset($expr);
        }
        return [$expr];
    }
    private function isNestedExpr(\PhpParser\Node\Expr $expr) : bool
    {
        return $expr instanceof \PhpParser\Node\Expr\Cast || $expr instanceof \PhpParser\Node\Expr\Empty_ || $expr instanceof \PhpParser\Node\Expr\UnaryMinus || $expr instanceof \PhpParser\Node\Expr\UnaryPlus || $expr instanceof \PhpParser\Node\Expr\BitwiseNot || $expr instanceof \PhpParser\Node\Expr\BooleanNot || $expr instanceof \PhpParser\Node\Expr\Clone_;
    }
    private function isBinaryOpWithoutChange(\PhpParser\Node\Expr $expr) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\BinaryOp) {
            return \false;
        }
        return !($expr instanceof \PhpParser\Node\Expr\BinaryOp\LogicalAnd || $expr instanceof \PhpParser\Node\Expr\BinaryOp\BooleanAnd || $expr instanceof \PhpParser\Node\Expr\BinaryOp\LogicalOr || $expr instanceof \PhpParser\Node\Expr\BinaryOp\BooleanOr || $expr instanceof \PhpParser\Node\Expr\BinaryOp\Coalesce);
    }
    /**
     * @return Expr[]
     */
    private function processBinary(\PhpParser\Node\Expr\BinaryOp $binaryOp) : array
    {
        return \array_merge($this->keepLivingCodeFromExpr($binaryOp->left), $this->keepLivingCodeFromExpr($binaryOp->right));
    }
    /**
     * @return mixed[]
     */
    private function processIsset(\PhpParser\Node\Expr\Isset_ $isset) : array
    {
        $livingExprs = [];
        foreach ($isset->vars as $expr) {
            $livingExprs = \array_merge($livingExprs, $this->keepLivingCodeFromExpr($expr));
        }
        return $livingExprs;
    }
}
