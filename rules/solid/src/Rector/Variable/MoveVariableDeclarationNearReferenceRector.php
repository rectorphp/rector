<?php

declare(strict_types=1);

namespace Rector\SOLID\Rector\Variable;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeNestingScope\ParentScopeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\SOLID\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\MoveVariableDeclarationNearReferenceRectorTest
 */
final class MoveVariableDeclarationNearReferenceRector extends AbstractRector
{
    /** @var ParentScopeFinder */
    private $parentScopeFinder;

    public function __construct(ParentScopeFinder $parentScopeFinder)
    {
        $this->parentScopeFinder = $parentScopeFinder;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Move variable declaration near its reference', [
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
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        $assign = $node;
        $variable = $node->var;
        if (!$variable instanceof Variable) {
            return null;
        }

        $parentScope = $this->parentScopeFinder->find($assign);
        if ($parentScope === null) {
            return null;
        }

        $usage = $this->findFirstVariableUsageInScope($variable, $assign, $parentScope);
        if ($usage === null) {
            return null;
        }

        $usageStatement = $usage->getAttribute(AttributeKey::CURRENT_STATEMENT);
        $assignStatement = $assign->getAttribute(AttributeKey::CURRENT_STATEMENT);

        $this->addNodeBeforeNode($assignStatement, $usageStatement);
        $this->removeNode($assignStatement);

        return $node;
    }

    /**
     * Find the first node within the same method being a usage of the assigned variable,
     * but not the original assignment itself.
     */
    private function findFirstVariableUsageInScope(Variable $variable, Assign $assign, Node $parentScope): ?Node
    {
        return $this->betterNodeFinder->findFirst(
            (array)$parentScope->getStmts(),
            function (Node $node) use ($variable, $assign): bool {
                return
                    $this->isVariable($node) &&
                    !$this->isOriginalAssign($node, $assign) &&
                    $this->betterStandardPrinter->areNodesEqual($node, $variable);
            }
        );
    }

    private function isOriginalAssign(Node $node, Assign $assign): bool
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        return $parentNode === $assign;
    }

    private function isVariable(Node $node): bool
    {
        return $node instanceof Variable;
    }
}
