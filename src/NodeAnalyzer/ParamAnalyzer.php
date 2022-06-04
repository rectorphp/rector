<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeTraverser;
use Rector\Core\NodeManipulator\FuncCallManipulator;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220604\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class ParamAnalyzer
{
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
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    public function __construct(\Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator, \Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Core\NodeManipulator\FuncCallManipulator $funcCallManipulator, \RectorPrefix20220604\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->nodeComparator = $nodeComparator;
        $this->valueResolver = $valueResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->funcCallManipulator = $funcCallManipulator;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    public function isParamUsedInClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Param $param) : bool
    {
        $isParamUsed = \false;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classMethod->stmts, function (\PhpParser\Node $node) use(&$isParamUsed, $param) : ?int {
            if ($this->isUsedAsArg($node, $param)) {
                $isParamUsed = \true;
            }
            // skip nested anonymous class
            if ($node instanceof \PhpParser\Node\Stmt\Class_ || $node instanceof \PhpParser\Node\Stmt\Function_) {
                return \PhpParser\NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($node instanceof \PhpParser\Node\Expr\Variable && $this->nodeComparator->areNodesEqual($node, $param->var)) {
                $isParamUsed = \true;
            }
            if ($node instanceof \PhpParser\Node\Expr\Closure && $this->isVariableInClosureUses($node, $param->var)) {
                $isParamUsed = \true;
            }
            if ($this->isParamUsed($node, $param)) {
                $isParamUsed = \true;
            }
            return null;
        });
        return $isParamUsed;
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
    private function isUsedAsArg(\PhpParser\Node $node, \PhpParser\Node\Param $param) : bool
    {
        if ($node instanceof \PhpParser\Node\Expr\New_ || $node instanceof \PhpParser\Node\Expr\CallLike) {
            foreach ($node->getArgs() as $arg) {
                if ($this->nodeComparator->areNodesEqual($param->var, $arg->value)) {
                    return \true;
                }
            }
        }
        return \false;
    }
    private function isParamUsed(\PhpParser\Node $node, \PhpParser\Node\Param $param) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\FuncCall) {
            return \false;
        }
        if (!$this->nodeNameResolver->isName($node, 'compact')) {
            return \false;
        }
        $arguments = $this->funcCallManipulator->extractArgumentsFromCompactFuncCalls([$node]);
        return $this->nodeNameResolver->isNames($param, $arguments);
    }
}
