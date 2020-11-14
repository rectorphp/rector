<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\For_;

use Doctrine\Inflector\Inflector;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Manipulator\AssignManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\CodeQuality\Tests\Rector\For_\ForToForeachRector\ForToForeachRectorTest
 */
final class ForToForeachRector extends AbstractRector
{
    /**
     * @var AssignManipulator
     */
    private $assignManipulator;

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

    public function __construct(AssignManipulator $assignManipulator, Inflector $inflector)
    {
        $this->assignManipulator = $assignManipulator;
        $this->inflector = $inflector;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change for() to foreach() where useful', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($tokens)
    {
        for ($i = 0, $c = count($tokens); $i < $c; ++$i) {
            if ($tokens[$i][0] === T_STRING && $tokens[$i][1] === 'fn') {
                $previousNonSpaceToken = $this->getPreviousNonSpaceToken($tokens, $i);
                if ($previousNonSpaceToken !== null && $previousNonSpaceToken[0] === T_OBJECT_OPERATOR) {
                    continue;
                }
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
                $previousNonSpaceToken = $this->getPreviousNonSpaceToken($tokens, $i);
                if ($previousNonSpaceToken !== null && $previousNonSpaceToken[0] === T_OBJECT_OPERATOR) {
                    continue;
                }
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

        $this->matchInit((array) $node->init);

        if (! $this->isConditionMatch((array) $node->cond)) {
            return null;
        }

        if (! $this->isLoopMatch((array) $node->loop)) {
            return null;
        }

        if ($this->iteratedExpr === null || $this->keyValueName === null) {
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

        if ($this->isCountValueVariableUsedInsideForStatements($node)) {
            return null;
        }

        $iteratedVariableSingle = $this->inflector->singularize($iteratedVariable);
        $foreach = $this->createForeach($node, $iteratedVariableSingle);

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

            if ($this->isValue($initExpr->expr, 0)) {
                $this->keyValueName = $this->getName($initExpr->var);
            }

            if ($this->isFuncCallName($initExpr->expr, 'count')) {
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
        if (count($condExprs) !== 1) {
            return false;
        }

        if ($this->keyValueName === null) {
            return false;
        }

        if ($this->countValueName !== null) {
            return $this->isSmallerOrGreater($condExprs, $this->keyValueName, $this->countValueName);
        }

        // count($values)
        if ($this->isFuncCallName($condExprs[0]->right, 'count')) {
            /** @var FuncCall $countFuncCall */
            $countFuncCall = $condExprs[0]->right;
            $this->iteratedExpr = $countFuncCall->args[0]->value;
            return true;
        }

        return false;
    }

    /**
     * @param Expr[] $loopExprs
     * $param
     */
    private function isLoopMatch(array $loopExprs): bool
    {
        if (count($loopExprs) !== 1) {
            return false;
        }

        if ($this->keyValueName === null) {
            return false;
        }

        if ($loopExprs[0] instanceof PreInc || $loopExprs[0] instanceof PostInc) {
            return $this->isName($loopExprs[0]->var, $this->keyValueName);
        }

        return false;
    }

    private function isCountValueVariableUsedInsideForStatements(For_ $for): bool
    {
        return (bool) $this->betterNodeFinder->findFirst(
            $for->stmts,
            function (Node $node): bool {
                return $this->areNodesEqual($this->countValueVariable, $node);
            }
        );
    }

    private function createForeach(For_ $for, string $iteratedVariableName): Foreach_
    {
        if ($this->iteratedExpr === null) {
            throw new ShouldNotHappenException();
        }

        if ($this->keyValueName === null) {
            throw new ShouldNotHappenException();
        }

        $foreach = new Foreach_($this->iteratedExpr, new Variable($iteratedVariableName));
        $foreach->stmts = $for->stmts;
        $foreach->keyVar = new Variable($this->keyValueName);

        return $foreach;
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

            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($this->assignManipulator->isNodePartOfAssign($parentNode)) {
                return null;
            }

            // is dim same as key value name, ...[$i]
            if (! $this->isVariableName($node->dim, $this->keyValueName)) {
                return null;
            }

            return $singleValue;
        });
    }

    /**
     * @param Expr[] $condExprs
     */
    private function isSmallerOrGreater(array $condExprs, string $keyValueName, string $countValueName): bool
    {
        // $i < $count
        if ($condExprs[0] instanceof Smaller) {
            if (! $this->isName($condExprs[0]->left, $keyValueName)) {
                return false;
            }

            return $this->isName($condExprs[0]->right, $countValueName);
        }

        // $i > $count
        if ($condExprs[0] instanceof Greater) {
            if (! $this->isName($condExprs[0]->left, $countValueName)) {
                return false;
            }

            return $this->isName($condExprs[0]->right, $keyValueName);
        }

        return false;
    }
}
