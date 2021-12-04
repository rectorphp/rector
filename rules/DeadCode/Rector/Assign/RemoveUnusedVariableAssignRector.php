<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Php\ReservedKeywordAnalyzer;
use Rector\Core\PhpParser\Comparing\ConditionSearcher;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeAnalyzer\ExprUsedInNextNodeAnalyzer;
use Rector\DeadCode\NodeAnalyzer\UsedVariableNameAnalyzer;
use Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\RemoveUnusedVariableAssignRectorTest
 */
final class RemoveUnusedVariableAssignRector extends AbstractRector
{
    public function __construct(
        private readonly ReservedKeywordAnalyzer $reservedKeywordAnalyzer,
        private readonly ConditionSearcher $conditionSearcher,
        private readonly UsedVariableNameAnalyzer $usedVariableNameAnalyzer,
        private readonly SideEffectNodeDetector $sideEffectNodeDetector,
        private readonly ExprUsedInNextNodeAnalyzer $exprUsedInNextNodeAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused assigns to variables', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = 5;
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $variable = $node->var;
        if (! $variable instanceof Variable) {
            return null;
        }

        $variableName = $this->getName($variable);
        if ($variableName !== null && $this->reservedKeywordAnalyzer->isNativeVariable($variableName)) {
            return null;
        }

        // variable is used
        if ($this->isUsed($node, $variable)) {
            return $this->refactorUsedVariable($node);
        }

        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Expression) {
            return null;
        }

        if ($this->sideEffectNodeDetector->detectCallExpr($node->expr)) {
            // keep the expr, can have side effect
            return $node->expr;
        }

        $this->removeNode($node);
        return $node;
    }

    private function shouldSkip(Assign $assign): bool
    {
        $classMethod = $this->betterNodeFinder->findParentType($assign, ClassMethod::class);
        if (! $classMethod instanceof FunctionLike) {
            return true;
        }

        $variable = $assign->var;
        if (! $variable instanceof Variable) {
            return true;
        }

        if (! $variable->name instanceof Variable) {
            return false;
        }

        return (bool) $this->betterNodeFinder->findFirstNext(
            $assign,
            fn (Node $node): bool => $node instanceof Variable
        );
    }

    private function isUsed(Assign $assign, Variable $variable): bool
    {
        $isUsedPrev = (bool) $this->betterNodeFinder->findFirstPreviousOfNode(
            $variable,
            fn (Node $node): bool => $this->usedVariableNameAnalyzer->isVariableNamed($node, $variable)
        );

        if ($isUsedPrev) {
            return true;
        }

        if ($this->exprUsedInNextNodeAnalyzer->isUsed($variable)) {
            return true;
        }

        /** @var FuncCall|MethodCall|New_|NullsafeMethodCall|StaticCall $expr */
        $expr = $assign->expr;
        if (! $this->sideEffectNodeDetector->detectCallExpr($expr)) {
            return false;
        }

        return $this->isUsedInAssignExpr($expr, $assign);
    }

    private function isUsedInAssignExpr(
        FuncCall | MethodCall | New_ | NullsafeMethodCall | StaticCall $expr,
        Assign $assign
    ): bool {
        foreach ($expr->args as $arg) {
            if (! $arg instanceof Arg) {
                continue;
            }

            $variable = $arg->value;
            if (! $variable instanceof Variable) {
                continue;
            }

            $previousAssign = $this->betterNodeFinder->findFirstPreviousOfNode(
                $assign,
                fn (Node $node): bool => $node instanceof Assign && $this->usedVariableNameAnalyzer->isVariableNamed(
                    $node->var,
                    $variable
                )
            );

            if ($previousAssign instanceof Assign) {
                return $this->isUsed($assign, $variable);
            }
        }

        return false;
    }

    private function refactorUsedVariable(Assign $assign): ?Assign
    {
        $parentNode = $assign->getAttribute(AttributeKey::PARENT_NODE);

        $if = $parentNode->getAttribute(AttributeKey::NEXT_NODE);

        // check if next node is if
        if (! $if instanceof If_) {
            return null;
        }

        if ($this->conditionSearcher->hasIfAndElseForVariableRedeclaration($assign, $if)) {
            $this->removeNode($assign);
            return $assign;
        }

        return null;
    }
}
