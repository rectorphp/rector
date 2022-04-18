<?php

declare (strict_types=1);
namespace Rector\DowngradePhp56\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220418\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/function.array-filter.php
 *
 * @see \Rector\Tests\DowngradePhp56\Rector\FuncCall\DowngradeArrayFilterUseConstantRector\DowngradeArrayFilterUseConstantRectorTest
 */
final class DowngradeArrayFilterUseConstantRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    public function __construct(\Rector\Naming\Naming\VariableNaming $variableNaming, \RectorPrefix20220418\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->variableNaming = $variableNaming;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace use ARRAY_FILTER_USE_BOTH and ARRAY_FILTER_USE_KEY to loop to filter it', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$arr = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];

var_dump(array_filter($arr, function($v, $k) {
    return $k == 'b' || $v == 4;
}, ARRAY_FILTER_USE_BOTH));
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$arr = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];

$result = [];
foreach ($arr as $k => $v) {
    if ($v === 4 || $k === 'b') {
        $result[$k] = $v;
    }
}

var_dump($result);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $args = $node->getArgs();
        if ($this->shouldSkip($node, $args)) {
            return null;
        }
        if ($args[1]->value instanceof \PhpParser\Node\Expr\Closure) {
            return $this->processClosure($node, $args);
        }
        return null;
    }
    /**
     * @param Arg[] $args
     */
    private function processClosure(\PhpParser\Node\Expr\FuncCall $funcCall, array $args) : ?\PhpParser\Node\Expr\Variable
    {
        /** @var Closure $closure */
        $closure = $args[1]->value;
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($closure, \PhpParser\Node\Stmt\Return_::class);
        if ($returns === []) {
            return null;
        }
        $currentStatement = $funcCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
        if (!$currentStatement instanceof \PhpParser\Node\Stmt) {
            return null;
        }
        $scope = $funcCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $variable = new \PhpParser\Node\Expr\Variable($this->variableNaming->createCountedValueName('result', $scope));
        $this->nodesToAddCollector->addNodeBeforeNode(new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign($variable, new \PhpParser\Node\Expr\Array_([]))), $currentStatement);
        /** @var ConstFetch $constant */
        $constant = $args[2]->value;
        $foreach = $this->nodeNameResolver->isName($constant, 'ARRAY_FILTER_USE_KEY') ? $this->applyArrayFilterUseKey($args, $closure, $variable) : $this->applyArrayFilterUseBoth($args, $closure, $variable);
        $this->nodesToAddCollector->addNodeBeforeNode($foreach, $currentStatement);
        return $variable;
    }
    /**
     * @param Arg[] $args
     */
    private function applyArrayFilterUseBoth(array $args, \PhpParser\Node\Expr\Closure $closure, \PhpParser\Node\Expr\Variable $variable) : \PhpParser\Node\Stmt\Foreach_
    {
        $arrayValue = $args[0]->value;
        $value = $closure->params[0]->var;
        $key = $closure->params[1]->var;
        $foreach = new \PhpParser\Node\Stmt\Foreach_($arrayValue, $value, ['keyVar' => $key]);
        $stmts = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($closure->stmts, function (\PhpParser\Node $subNode) use($variable, $key, $value, &$stmts) {
            if (!$subNode instanceof \PhpParser\Node\Stmt) {
                return null;
            }
            if (!$subNode instanceof \PhpParser\Node\Stmt\Return_) {
                $stmts[] = $subNode;
                return null;
            }
            if (!$subNode->expr instanceof \PhpParser\Node\Expr) {
                $stmts[] = $subNode;
                return null;
            }
            $assign = new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\ArrayDimFetch($variable, $key), $value);
            $stmts[] = new \PhpParser\Node\Stmt\If_($subNode->expr, ['stmts' => [new \PhpParser\Node\Stmt\Expression($assign)]]);
            return null;
        });
        $foreach->stmts = $stmts;
        return $foreach;
    }
    /**
     * @param Arg[] $args
     */
    private function applyArrayFilterUseKey(array $args, \PhpParser\Node\Expr\Closure $closure, \PhpParser\Node\Expr\Variable $variable) : \PhpParser\Node\Stmt\Foreach_
    {
        $arrayValue = $args[0]->value;
        $funcCall = $this->nodeFactory->createFuncCall('array_keys', [$arrayValue]);
        $key = $closure->params[0]->var;
        $foreach = new \PhpParser\Node\Stmt\Foreach_($funcCall, $key);
        $stmts = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($closure->stmts, function (\PhpParser\Node $subNode) use($variable, $key, $arrayValue, &$stmts) {
            if (!$subNode instanceof \PhpParser\Node\Stmt) {
                return null;
            }
            if (!$subNode instanceof \PhpParser\Node\Stmt\Return_) {
                $stmts[] = $subNode;
                return null;
            }
            if (!$subNode->expr instanceof \PhpParser\Node\Expr) {
                $stmts[] = $subNode;
                return null;
            }
            $assign = new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\ArrayDimFetch($variable, $key), new \PhpParser\Node\Expr\ArrayDimFetch($arrayValue, $key));
            $stmts[] = new \PhpParser\Node\Stmt\If_($subNode->expr, ['stmts' => [new \PhpParser\Node\Stmt\Expression($assign)]]);
            return null;
        });
        $foreach->stmts = $stmts;
        return $foreach;
    }
    /**
     * @param Arg[] $args
     */
    private function shouldSkip(\PhpParser\Node\Expr\FuncCall $funcCall, array $args) : bool
    {
        if (!$this->nodeNameResolver->isName($funcCall, 'array_filter')) {
            return \true;
        }
        if (!isset($args[2])) {
            return \true;
        }
        if (!$args[2]->value instanceof \PhpParser\Node\Expr\ConstFetch) {
            return \true;
        }
        return !$this->nodeNameResolver->isNames($args[2]->value, ['ARRAY_FILTER_USE_KEY', 'ARRAY_FILTER_USE_BOTH']);
    }
}
