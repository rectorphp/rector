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
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PostRector\Collector\NodesToAddCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/function.array-filter.php
 *
 * @see \Rector\Tests\DowngradePhp56\Rector\FuncCall\DowngradeArrayFilterUseConstantRector\DowngradeArrayFilterUseConstantRectorTest
 */
final class DowngradeArrayFilterUseConstantRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    public function __construct(VariableNaming $variableNaming, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodesToAddCollector $nodesToAddCollector)
    {
        $this->variableNaming = $variableNaming;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodesToAddCollector = $nodesToAddCollector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace use ARRAY_FILTER_USE_BOTH and ARRAY_FILTER_USE_KEY to loop to filter it', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        $args = $node->getArgs();
        if ($this->shouldSkip($node, $args)) {
            return null;
        }
        if ($args[1]->value instanceof Closure) {
            return $this->processClosure($node, $args);
        }
        return null;
    }
    /**
     * @param Arg[] $args
     */
    private function processClosure(FuncCall $funcCall, array $args) : ?Variable
    {
        /** @var Closure $closure */
        $closure = $args[1]->value;
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($closure, Return_::class);
        if ($returns === []) {
            return null;
        }
        $scope = $funcCall->getAttribute(AttributeKey::SCOPE);
        $variable = new Variable($this->variableNaming->createCountedValueName('result', $scope));
        $this->nodesToAddCollector->addNodeBeforeNode(new Expression(new Assign($variable, new Array_([]))), $funcCall);
        /** @var ConstFetch $constant */
        $constant = $args[2]->value;
        $foreach = $this->nodeNameResolver->isName($constant, 'ARRAY_FILTER_USE_KEY') ? $this->applyArrayFilterUseKey($args, $closure, $variable) : $this->applyArrayFilterUseBoth($args, $closure, $variable);
        $this->nodesToAddCollector->addNodeBeforeNode($foreach, $funcCall);
        return $variable;
    }
    /**
     * @param Arg[] $args
     */
    private function applyArrayFilterUseBoth(array $args, Closure $closure, Variable $variable) : Foreach_
    {
        $arrayValue = $args[0]->value;
        $value = $closure->params[0]->var;
        $key = $closure->params[1]->var;
        $foreach = new Foreach_($arrayValue, $value, ['keyVar' => $key]);
        $stmts = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($closure->stmts, static function (Node $subNode) use($variable, $key, $value, &$stmts) {
            if (!$subNode instanceof Stmt) {
                return null;
            }
            if (!$subNode instanceof Return_) {
                $stmts[] = $subNode;
                return null;
            }
            if (!$subNode->expr instanceof Expr) {
                $stmts[] = $subNode;
                return null;
            }
            $assign = new Assign(new ArrayDimFetch($variable, $key), $value);
            $stmts[] = new If_($subNode->expr, ['stmts' => [new Expression($assign)]]);
            return null;
        });
        $foreach->stmts = $stmts;
        return $foreach;
    }
    /**
     * @param Arg[] $args
     */
    private function applyArrayFilterUseKey(array $args, Closure $closure, Variable $variable) : Foreach_
    {
        $arrayValue = $args[0]->value;
        $funcCall = $this->nodeFactory->createFuncCall('array_keys', [$arrayValue]);
        $key = $closure->params[0]->var;
        $foreach = new Foreach_($funcCall, $key);
        $stmts = [];
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($closure->stmts, static function (Node $subNode) use($variable, $key, $arrayValue, &$stmts) {
            if (!$subNode instanceof Stmt) {
                return null;
            }
            if (!$subNode instanceof Return_) {
                $stmts[] = $subNode;
                return null;
            }
            if (!$subNode->expr instanceof Expr) {
                $stmts[] = $subNode;
                return null;
            }
            $assign = new Assign(new ArrayDimFetch($variable, $key), new ArrayDimFetch($arrayValue, $key));
            $stmts[] = new If_($subNode->expr, ['stmts' => [new Expression($assign)]]);
            return null;
        });
        $foreach->stmts = $stmts;
        return $foreach;
    }
    /**
     * @param Arg[] $args
     */
    private function shouldSkip(FuncCall $funcCall, array $args) : bool
    {
        if (!$this->nodeNameResolver->isName($funcCall, 'array_filter')) {
            return \true;
        }
        if (!isset($args[2])) {
            return \true;
        }
        if (!$args[2]->value instanceof ConstFetch) {
            return \true;
        }
        return !$this->nodeNameResolver->isNames($args[2]->value, ['ARRAY_FILTER_USE_KEY', 'ARRAY_FILTER_USE_BOTH']);
    }
}
