<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\NodeAnalyzer\CompactFuncCallAnalyzer;
use Rector\Core\Php\ReservedKeywordAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\RemoveUnusedVariableAssignRectorTest
 */
final class RemoveUnusedVariableAssignRector extends AbstractRector
{
    /**
     * @var ReservedKeywordAnalyzer
     */
    private $reservedKeywordAnalyzer;

    /**
     * @var CompactFuncCallAnalyzer
     */
    private $compactFuncCallAnalyzer;

    public function __construct(
        ReservedKeywordAnalyzer $reservedKeywordAnalyzer,
        CompactFuncCallAnalyzer $compactFuncCallAnalyzer
    ) {
        $this->reservedKeywordAnalyzer = $reservedKeywordAnalyzer;
        $this->compactFuncCallAnalyzer = $compactFuncCallAnalyzer;
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
        $classMethod = $node->getAttribute(AttributeKey::METHOD_NODE);
        if (! $classMethod instanceof FunctionLike) {
            return null;
        }

        $variable = $node->var;
        if (! $variable instanceof Variable) {
            return null;
        }

        // variable is used
        if ($this->isUsed($node, $variable)) {
            return null;
        }

        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Expression) {
            return null;
        }

        if (is_string($variable->name) && $this->reservedKeywordAnalyzer->isNativeVariable($variable->name)) {
            return null;
        }

        if ($node->expr instanceof MethodCall || $node->expr instanceof StaticCall) {
            // keep the expr, can have side effect
            return $node->expr;
        }

        $this->removeNode($node);
        return $node;
    }

    private function isUsed(Assign $assign, Variable $variable): bool
    {
        $isUsedPrev = (bool) $this->betterNodeFinder->findFirstPreviousOfNode($variable, function (Node $node) use (
            $variable
        ): bool {
            return $this->isVariableNamed($node, $variable);
        });

        if ($isUsedPrev) {
            return true;
        }

        $isUsedNext = (bool) $this->betterNodeFinder->findFirstNext($variable, function (Node $node) use (
            $variable
        ): bool {
            if ($this->isVariableNamed($node, $variable)) {
                return true;
            }

            if ($node instanceof FuncCall) {
                return $this->compactFuncCallAnalyzer->isInCompact($node, $variable);
            }

            return false;
        });

        if ($isUsedNext) {
            return true;
        }

        /** @var FuncCall|MethodCall|New_|NullsafeMethodCall|StaticCall $expr */
        $expr = $assign->expr;
        if (! $this->isCall($expr)) {
            return false;
        }

        return $this->isUsedInAssignExpr($expr, $assign);
    }

    /**
     * @param FuncCall|MethodCall|New_|NullsafeMethodCall|StaticCall $expr
     */
    private function isUsedInAssignExpr(Expr $expr, Assign $assign): bool
    {
        $args = $expr->args;
        foreach ($args as $arg) {
            $variable = $arg->value;
            if (! $variable instanceof Variable) {
                continue;
            }

            $previousAssign = $this->betterNodeFinder->findFirstPreviousOfNode($assign, function (Node $node) use (
                $variable
            ): bool {
                return $node instanceof Assign && $this->isVariableNamed($node->var, $variable);
            });
            if ($previousAssign instanceof Assign) {
                return $this->isUsed($assign, $variable);
            }
        }

        return false;
    }

    private function isCall(Expr $expr): bool
    {
        return $expr instanceof FuncCall || $expr instanceof MethodCall || $expr instanceof New_ || $expr instanceof NullsafeMethodCall || $expr instanceof StaticCall;
    }

    private function isVariableNamed(Node $node, Variable $variable): bool
    {
        if ($node instanceof MethodCall && $node->name instanceof Variable && is_string($node->name->name)) {
            return $this->isName($variable, $node->name->name);
        }

        if ($node instanceof PropertyFetch && $node->name instanceof Variable && is_string($node->name->name)) {
            return $this->isName($variable, $node->name->name);
        }

        if (! $node instanceof Variable) {
            return false;
        }

        return $this->isName($variable, (string) $this->getName($node));
    }
}
