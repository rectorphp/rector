<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
final class ParamAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
        $this->valueResolver = $valueResolver;
    }
    public function isParamUsedInClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Param $param) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (\PhpParser\Node $node) use($param) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\Variable) {
                return \false;
            }
            return $this->nodeComparator->areNodesEqual($node, $param->var);
        });
    }
    /**
     * @param Param[] $params
     */
    public function hasPropertyPromotion(array $params) : bool
    {
        foreach ($params as $param) {
            if ($param->flags !== 0) {
                return \true;
            }
        }
        return \false;
    }
    public function isNullable(\PhpParser\Node\Param $param) : bool
    {
        if ($param->variadic) {
            return \false;
        }
        if ($param->type === null) {
            return \false;
        }
        return $param->type instanceof \PhpParser\Node\NullableType;
    }
    public function hasDefaultNull(\PhpParser\Node\Param $param) : bool
    {
        return $param->default instanceof \PhpParser\Node\Expr\ConstFetch && $this->valueResolver->isNull($param->default);
    }
}
