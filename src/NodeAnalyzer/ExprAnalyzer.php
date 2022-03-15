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
    public function __construct(\Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\NodeManipulator\ArrayManipulator $arrayManipulator)
    {
        $this->nodeComparator = $nodeComparator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->arrayManipulator = $arrayManipulator;
    }
    public function isNonTypedFromParam(\PhpParser\Node\Expr $expr) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        $functionLike = $this->betterNodeFinder->findParentType($expr, \PhpParser\Node\FunctionLike::class);
        if (!$functionLike instanceof \PhpParser\Node\FunctionLike) {
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
            return $paramTag instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode && $param->type === null;
        }
        return \false;
    }
    public function isDynamicExpr(\PhpParser\Node\Expr $expr) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\Array_) {
            if ($expr instanceof \PhpParser\Node\Scalar) {
                return \false;
            }
            return !$this->isAllowedConstFetchOrClassConstFetch($expr);
        }
        return $this->arrayManipulator->isDynamicArray($expr);
    }
    private function isAllowedConstFetchOrClassConstFetch(\PhpParser\Node\Expr $expr) : bool
    {
        if ($expr instanceof \PhpParser\Node\Expr\ConstFetch) {
            return \true;
        }
        if ($expr instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return $expr->class instanceof \PhpParser\Node\Name && $expr->name instanceof \PhpParser\Node\Identifier;
        }
        return \false;
    }
}
