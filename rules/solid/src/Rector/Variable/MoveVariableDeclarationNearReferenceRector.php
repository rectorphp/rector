<?php

declare(strict_types=1);

namespace Rector\SOLID\Rector\Variable;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\While_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\SOLID\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\MoveVariableDeclarationNearReferenceRectorTest
 */
final class MoveVariableDeclarationNearReferenceRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Move variable declaration near its reference',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$var = 1;
if ($condition === null) {
    return $var;
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
if ($condition === null) {
    $var = 1;
    return $var;
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
        return [Variable::class];
    }

    /**
     * @param Variable $node
     */
    public function refactor(Node $node): ?Node
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! ($parent instanceof Assign && $parent->var === $node)) {
            return null;
        }

        /** @var Expression */
        $expression = $parent->getAttribute(AttributeKey::PARENT_NODE);
        if (! $expression instanceof Expression) {
            return null;
        }

        if ($this->isUsedInParentPrev($expression, $node)) {
            return null;
        }

        $usages = $this->getUsageInNextStmts($expression, $node);
        if ($usages === []) {
            return null;
        }

        if (count($usages) === 1) {
            /** @var Node $parentUsage */
            $parentUsage = $usages[0]->getAttribute(AttributeKey::PARENT_NODE);
            // skip re-assign
            if ($parentUsage instanceof Assign) {
                return null;
            }

            /** @var Node $usageStmt */
            $usageStmt = $usages[0]->getAttribute(AttributeKey::CURRENT_STATEMENT);
            if ($this->isInsideLoopStmts($usageStmt, $node)) {
                return null;
            }

            $this->addNodeBeforeNode($expression, $usageStmt);
            $this->removeNode($expression);

            return $node;
        }

        return null;
    }

    private function isInsideLoopStmts(Node $node): bool
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);

        while ($parent) {
            if ($parent instanceof For_ || $parent instanceof While_ || $parent instanceof Foreach_ || $parent instanceof Do_) {
                return true;
            }

            $parent = $parent->getAttribute(AttributeKey::PARENT_NODE);
        }

        return false;
    }

    private function isUsedInParentPrev(Expression $expression, Variable $variable)
    {
        $parentExpression = $expression->getAttribute(AttributeKey::PARENT_NODE);
        while ($parentExpression) {
            $previous = $parentExpression->getAttribute(AttributeKey::PREVIOUS_NODE);
            if (! $previous instanceof Node) {
                $parentExpression = $parentExpression->getAttribute(AttributeKey::PARENT_NODE);

                continue;
            }

            $foundInPrev = $this->betterNodeFinder->find($previous, function (Node $node) use ($variable): bool {
                return $this->areNodesEqual($node, $variable);
            });

            if ($foundInPrev) {
                return true;
            }

            $parentExpression = $parentExpression->getAttribute(AttributeKey::PARENT_NODE);
        }

        return false;
    }

    /**
     * @return Variable[]
     */
    private function getUsageInNextStmts(Expression $expression, Variable $variable): array
    {
        /** @var Node|null $next */
        $next = $expression->getAttribute(AttributeKey::NEXT_NODE);

        $usages = [];
        while ($next) {
            $usages = array_merge($usages, $this->betterNodeFinder->find($next, function (Node $node) use (
                $variable
            ): bool {
                return $this->areNodesEqual($node, $variable);
            }));

            /** @var Node|null $next */
            $next = $next->getAttribute(AttributeKey::NEXT_NODE);
        }

        return $usages;
    }
}
