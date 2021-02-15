<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\For_;

use Doctrine\Inflector\Inflector;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use Rector\CodeQuality\NodeAnalyzer\ForNodeAnalyzer;
use Rector\CodeQuality\NodeFactory\ForeachFactory;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\For_\ForToForeachRector\ForToForeachRectorTest
 */
final class ForToForeachRector extends AbstractRector
{
    /**
     * @var string
     */
    private const COUNT = 'count';

    /**
     * @var Inflector
     */
    private $inflector;

    /**
     * @var string|null
     */
    private $keyValueName;

    /**
     * @var string|null
     */
    private $countValueName;

    /**
     * @var Expr|null
     */
    private $countValueVariable;

    /**
     * @var Expr|null
     */
    private $iteratedExpr;

    /**
     * @var ForNodeAnalyzer
     */
    private $forNodeAnalyzer;

    /**
     * @var ForeachFactory
     */
    private $foreachFactory;

    public function __construct(
        Inflector $inflector,
        ForNodeAnalyzer $forNodeAnalyzer,
        ForeachFactory $foreachFactory
    ) {
        $this->inflector = $inflector;
        $this->forNodeAnalyzer = $forNodeAnalyzer;
        $this->foreachFactory = $foreachFactory;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change for() to foreach() where useful', [
            new CodeSample(
                <<<'CODE_SAMPLE'
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
                ,
                <<<'CODE_SAMPLE'
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
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [For_::class];
    }

    /**
     * @param For_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->reset();

        $this->matchInit($node->init);

        if (! $this->isConditionMatch($node->cond)) {
            return null;
        }

        if (! $this->forNodeAnalyzer->isLoopMatch($node->loop, $this->keyValueName)) {
            return null;
        }

        if ($this->iteratedExpr === null) {
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
        if (count($init) > 2) {
            return null;
        }

        if ($this->forNodeAnalyzer->isCountValueVariableUsedInsideForStatements($node, $this->countValueVariable)) {
            return null;
        }

        if ($this->forNodeAnalyzer->isAssignmentWithArrayDimFetchAsVariableInsideForStatements(
            $node,
            $this->keyValueName
        )) {
            return null;
        }

        if ($this->forNodeAnalyzer->isArrayWithKeyValueNameUnsetted($node)) {
            return null;
        }

        return $this->processForToForeach($node, $iteratedVariable);
    }

    private function processForToForeach(For_ $for, string $iteratedVariable): ?Foreach_
    {
        $originalVariableSingle = $this->inflector->singularize($iteratedVariable);
        $iteratedVariableSingle = $originalVariableSingle;
        if ($iteratedVariableSingle === $iteratedVariable) {
            $iteratedVariableSingle = 'single' . ucfirst($iteratedVariableSingle);
        }

        if (! $this->forNodeAnalyzer->isValueVarUsedNext($for, $iteratedVariableSingle)) {
            return $this->createForeachFromForWithIteratedVariableSingle($for, $iteratedVariableSingle);
        }

        if ($iteratedVariableSingle === $originalVariableSingle) {
            return null;
        }

        if (! $this->forNodeAnalyzer->isValueVarUsedNext($for, $originalVariableSingle)) {
            return $this->createForeachFromForWithIteratedVariableSingle($for, $originalVariableSingle);
        }

        return null;
    }

    private function createForeachFromForWithIteratedVariableSingle(For_ $for, string $iteratedVariableSingle): Foreach_
    {
        $foreach = $this->foreachFactory->createFromFor(
            $for,
            $iteratedVariableSingle,
            $this->iteratedExpr,
            $this->keyValueName
        );
        $this->mirrorComments($foreach, $for);

        $this->useForeachVariableInStmts($foreach->expr, $foreach->valueVar, $foreach->stmts);

        return $foreach;
    }

    private function reset(): void
    {
        $this->keyValueName = null;
        $this->countValueVariable = null;
        $this->countValueName = null;
        $this->iteratedExpr = null;
    }

    /**
     * @param Expr[] $initExprs
     */
    private function matchInit(array $initExprs): void
    {
        foreach ($initExprs as $initExpr) {
            if (! $initExpr instanceof Assign) {
                continue;
            }

            if ($this->valueResolver->isValue($initExpr->expr, 0)) {
                $this->keyValueName = $this->getName($initExpr->var);
            }

            if ($this->isFuncCallName($initExpr->expr, self::COUNT)) {
                $this->countValueVariable = $initExpr->var;
                $this->countValueName = $this->getName($initExpr->var);
                $this->iteratedExpr = $initExpr->expr->args[0]->value;
            }
        }
    }

    /**
     * @param Expr[] $condExprs
     */
    private function isConditionMatch(array $condExprs): bool
    {
        if ($this->forNodeAnalyzer->isCondExprOneOrKeyValueNameNotNull($condExprs, $this->keyValueName)) {
            return false;
        }

        /** @var string $keyValueName */
        $keyValueName = $this->keyValueName;
        if ($this->countValueName !== null) {
            return $this->forNodeAnalyzer->isCondExprSmallerOrGreater(
                $condExprs,
                $keyValueName,
                $this->countValueName
            );
        }

        if (! $condExprs[0] instanceof BinaryOp) {
            return false;
        }

        // count($values)
        if ($this->isFuncCallName($condExprs[0]->right, self::COUNT)) {
            /** @var FuncCall $countFuncCall */
            $countFuncCall = $condExprs[0]->right;
            $this->iteratedExpr = $countFuncCall->args[0]->value;
            return true;
        }

        return false;
    }

    /**
     * @param Stmt[] $stmts
     */
    private function useForeachVariableInStmts(Expr $foreachedValue, Expr $singleValue, array $stmts): void
    {
        if ($this->keyValueName === null) {
            throw new ShouldNotHappenException();
        }

        $this->traverseNodesWithCallable($stmts, function (Node $node) use ($foreachedValue, $singleValue): ?Expr {
            if (! $node instanceof ArrayDimFetch) {
                return null;
            }

            // must be the same as foreach value
            if (! $this->areNodesEqual($node->var, $foreachedValue)) {
                return null;
            }

            if ($this->forNodeAnalyzer->isArrayDimFetchPartOfAssignOrArgParentCount($node)) {
                return null;
            }

            // is dim same as key value name, ...[$i]
            if ($this->keyValueName === null) {
                throw new ShouldNotHappenException();
            }

            if ($node->dim === null) {
                return null;
            }

            if (! $this->isVariableName($node->dim, $this->keyValueName)) {
                return null;
            }

            return $singleValue;
        });
    }
}
