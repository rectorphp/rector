<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\For_;

use RectorPrefix202305\Doctrine\Inflector\Inflector;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use Rector\CodeQuality\NodeAnalyzer\ForAnalyzer;
use Rector\CodeQuality\NodeAnalyzer\ForeachAnalyzer;
use Rector\CodeQuality\NodeAnalyzer\VariableNameUsedNextAnalyzer;
use Rector\CodeQuality\NodeFactory\ForeachFactory;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\For_\ForToForeachRector\ForToForeachRectorTest
 */
final class ForToForeachRector extends AbstractRector
{
    /**
     * @var string
     */
    private const COUNT = 'count';
    /**
     * @var string|null
     */
    private $keyValueName;
    /**
     * @var string|null
     */
    private $countValueName;
    /**
     * @var \PhpParser\Node\Expr|null
     */
    private $countValueVariableExpr;
    /**
     * @var \PhpParser\Node\Expr|null
     */
    private $iteratedExpr;
    /**
     * @readonly
     * @var \Doctrine\Inflector\Inflector
     */
    private $inflector;
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeAnalyzer\ForAnalyzer
     */
    private $forAnalyzer;
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeFactory\ForeachFactory
     */
    private $foreachFactory;
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeAnalyzer\ForeachAnalyzer
     */
    private $foreachAnalyzer;
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeAnalyzer\VariableNameUsedNextAnalyzer
     */
    private $variableNameUsedNextAnalyzer;
    public function __construct(Inflector $inflector, ForAnalyzer $forAnalyzer, ForeachFactory $foreachFactory, ForeachAnalyzer $foreachAnalyzer, VariableNameUsedNextAnalyzer $variableNameUsedNextAnalyzer)
    {
        $this->inflector = $inflector;
        $this->forAnalyzer = $forAnalyzer;
        $this->foreachFactory = $foreachFactory;
        $this->foreachAnalyzer = $foreachAnalyzer;
        $this->variableNameUsedNextAnalyzer = $variableNameUsedNextAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change for() to foreach() where useful', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($tokens)
    {
        for ($i = 0, $c = count($tokens); $i < $c; ++$i) {
            if ($tokens[$i][0] === T_STRING && $tokens[$i][1] === 'fn') {
                $tokens[$i][0] = self::T_FN;
            }
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($tokens)
    {
        foreach ($tokens as $i => $token) {
            if ($token[0] === T_STRING && $token[1] === 'fn') {
                $tokens[$i][0] = self::T_FN;
            }
        }
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [For_::class];
    }
    /**
     * @param For_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $this->reset();
        $this->matchInit($node->init);
        if (!$this->isConditionMatch($node->cond)) {
            return null;
        }
        if (!$this->forAnalyzer->isLoopMatch($node->loop, $this->keyValueName)) {
            return null;
        }
        if (!$this->iteratedExpr instanceof Expr) {
            return null;
        }
        if ($this->keyValueName === null) {
            return null;
        }
        $iteratedVariable = $this->getName($this->iteratedExpr);
        if ($iteratedVariable === null) {
            return null;
        }
        $init = $node->init;
        if (\count($init) > 2) {
            return null;
        }
        if ($this->forAnalyzer->isCountValueVariableUsedInsideForStatements($node, $this->countValueVariableExpr)) {
            return null;
        }
        if ($this->forAnalyzer->isAssignmentWithArrayDimFetchAsVariableInsideForStatements($node, $this->keyValueName)) {
            return null;
        }
        if ($this->forAnalyzer->isArrayWithKeyValueNameUnsetted($node)) {
            return null;
        }
        return $this->processForToForeach($node, $iteratedVariable);
    }
    private function processForToForeach(For_ $for, string $iteratedVariable) : ?Foreach_
    {
        $originalVariableSingle = $this->inflector->singularize($iteratedVariable);
        $iteratedVariableSingle = $originalVariableSingle;
        if ($iteratedVariableSingle === $iteratedVariable) {
            $iteratedVariableSingle = 'single' . \ucfirst($iteratedVariableSingle);
        }
        if (!$this->variableNameUsedNextAnalyzer->isValueVarUsedNext($for, $iteratedVariableSingle)) {
            return $this->createForeachFromForWithIteratedVariableSingle($for, $iteratedVariableSingle);
        }
        if ($iteratedVariableSingle === $originalVariableSingle) {
            return null;
        }
        if (!$this->variableNameUsedNextAnalyzer->isValueVarUsedNext($for, $originalVariableSingle)) {
            return $this->createForeachFromForWithIteratedVariableSingle($for, $originalVariableSingle);
        }
        return null;
    }
    private function createForeachFromForWithIteratedVariableSingle(For_ $for, string $iteratedVariableSingle) : Foreach_
    {
        $foreach = $this->foreachFactory->createFromFor($for, $iteratedVariableSingle, $this->iteratedExpr, $this->keyValueName);
        $this->mirrorComments($foreach, $for);
        if ($this->keyValueName === null) {
            throw new ShouldNotHappenException();
        }
        $this->foreachAnalyzer->useForeachVariableInStmts($foreach->expr, $foreach->valueVar, $foreach->stmts, $this->keyValueName);
        return $foreach;
    }
    private function reset() : void
    {
        $this->keyValueName = null;
        $this->countValueVariableExpr = null;
        $this->countValueName = null;
        $this->iteratedExpr = null;
    }
    /**
     * @param Expr[] $initExprs
     */
    private function matchInit(array $initExprs) : void
    {
        foreach ($initExprs as $initExpr) {
            if (!$initExpr instanceof Assign) {
                continue;
            }
            if ($this->valueResolver->isValue($initExpr->expr, 0)) {
                $this->keyValueName = $this->getName($initExpr->var);
            }
            if (!$initExpr->expr instanceof FuncCall) {
                continue;
            }
            $funcCall = $initExpr->expr;
            if ($this->nodeNameResolver->isName($funcCall, self::COUNT) && $funcCall->args[0] instanceof Arg) {
                $this->countValueVariableExpr = $initExpr->var;
                $this->countValueName = $this->getName($initExpr->var);
                $this->iteratedExpr = $funcCall->args[0]->value;
            }
        }
    }
    /**
     * @param Expr[] $condExprs
     */
    private function isConditionMatch(array $condExprs) : bool
    {
        if ($this->forAnalyzer->isCondExprOneOrKeyValueNameNotNull($condExprs, $this->keyValueName)) {
            return \false;
        }
        /** @var string $keyValueName */
        $keyValueName = $this->keyValueName;
        if ($this->countValueName !== null) {
            return $this->forAnalyzer->isCondExprSmallerOrGreater($condExprs, $keyValueName, $this->countValueName);
        }
        if (!$condExprs[0] instanceof BinaryOp) {
            return \false;
        }
        $funcCall = $condExprs[0]->right;
        if (!$funcCall instanceof FuncCall) {
            return \false;
        }
        if ($this->nodeNameResolver->isName($funcCall, self::COUNT) && $funcCall->args[0] instanceof Arg) {
            $this->iteratedExpr = $funcCall->args[0]->value;
            return \true;
        }
        return \false;
    }
}
