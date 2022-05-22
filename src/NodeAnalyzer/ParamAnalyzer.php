<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\NodeManipulator\FuncCallManipulator;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;
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
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\FuncCallManipulator
     */
    private $funcCallManipulator;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\NodeManipulator\FuncCallManipulator $funcCallManipulator)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
        $this->valueResolver = $valueResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->funcCallManipulator = $funcCallManipulator;
    }
    public function isParamUsedInClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Param $param) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($classMethod, function (\PhpParser\Node $node) use($param) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\Variable && !$node instanceof \PhpParser\Node\Expr\Closure && !$node instanceof \PhpParser\Node\Expr\FuncCall) {
                return \false;
            }
            if ($node instanceof \PhpParser\Node\Expr\Variable) {
                return $this->nodeComparator->areNodesEqual($node, $param->var);
            }
            if ($node instanceof \PhpParser\Node\Expr\Closure) {
                return $this->isVariableInClosureUses($node, $param->var);
            }
            if (!$this->nodeNameResolver->isName($node, 'compact')) {
                return \false;
            }
            $arguments = $this->funcCallManipulator->extractArgumentsFromCompactFuncCalls([$node]);
            return $this->nodeNameResolver->isNames($param, $arguments);
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
    private function isVariableInClosureUses(\PhpParser\Node\Expr\Closure $closure, \PhpParser\Node\Expr\Variable $variable) : bool
    {
        foreach ($closure->uses as $use) {
            if ($this->nodeComparator->areNodesEqual($use->var, $variable)) {
                return \true;
            }
        }
        return \false;
    }
}
