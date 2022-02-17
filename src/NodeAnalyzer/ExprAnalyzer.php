<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
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
    public function __construct(\Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeComparator = $nodeComparator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeNameResolver = $nodeNameResolver;
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
    public function isDynamicArray(\PhpParser\Node\Expr\Array_ $array) : bool
    {
        foreach ($array->items as $item) {
            if (!$item instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            $key = $item->key;
            if (!$this->isAllowedArrayKey($key)) {
                return \true;
            }
            $value = $item->value;
            if (!$this->isAllowedArrayValue($value)) {
                return \true;
            }
        }
        return \false;
    }
    private function isAllowedArrayKey(?\PhpParser\Node\Expr $expr) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr) {
            return \true;
        }
        return \in_array(\get_class($expr), [\PhpParser\Node\Scalar\String_::class, \PhpParser\Node\Scalar\LNumber::class], \true);
    }
    private function isAllowedArrayValue(\PhpParser\Node\Expr $expr) : bool
    {
        if ($expr instanceof \PhpParser\Node\Expr\Array_) {
            return \true;
        }
        return $this->isAllowedArrayOrScalar($expr);
    }
    private function isAllowedArrayOrScalar(\PhpParser\Node\Expr $expr) : bool
    {
        if (!$expr instanceof \PhpParser\Node\Expr\Array_) {
            return $expr instanceof \PhpParser\Node\Scalar;
        }
        return !$this->isDynamicArray($expr);
    }
}
