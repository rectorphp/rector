<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\Encapsed;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\NodeManipulator\ArrayManipulator;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
final class ExprAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ArrayManipulator
     */
    private $arrayManipulator;
    public function __construct(NodeComparator $nodeComparator, BetterNodeFinder $betterNodeFinder, PhpDocInfoFactory $phpDocInfoFactory, NodeNameResolver $nodeNameResolver, ArrayManipulator $arrayManipulator)
    {
        $this->nodeComparator = $nodeComparator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->arrayManipulator = $arrayManipulator;
    }
    public function isNonTypedFromParam(Expr $expr) : bool
    {
        if (!$expr instanceof Variable) {
            return \false;
        }
        $functionLike = $this->betterNodeFinder->findParentType($expr, FunctionLike::class);
        if (!$functionLike instanceof FunctionLike) {
            return \false;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $params = $functionLike->getParams();
        foreach ($params as $param) {
            if (!$this->nodeComparator->areNodesEqual($param->var, $expr)) {
                continue;
            }
            $paramName = $this->nodeNameResolver->getName($param->var);
            if ($paramName === null) {
                continue;
            }
            $paramTag = $phpDocInfo->getParamTagValueByName($paramName);
            return $paramTag instanceof ParamTagValueNode && $param->type === null;
        }
        return \false;
    }
    public function isDynamicExpr(Expr $expr) : bool
    {
        if (!$expr instanceof Array_) {
            if ($expr instanceof Scalar) {
                // string interpolation is true, otherwise false
                return $expr instanceof Encapsed;
            }
            return !$this->isAllowedConstFetchOrClassConstFetch($expr);
        }
        return $this->arrayManipulator->isDynamicArray($expr);
    }
    private function isAllowedConstFetchOrClassConstFetch(Expr $expr) : bool
    {
        if ($expr instanceof ConstFetch) {
            return \true;
        }
        if ($expr instanceof ClassConstFetch) {
            return $expr->class instanceof Name && $expr->name instanceof Identifier;
        }
        return \false;
    }
}
