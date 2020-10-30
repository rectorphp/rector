<?php

declare(strict_types=1);

namespace Rector\SOLID\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeNestingScope\ParentScopeFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\SOLID\Tests\Rector\Assign\MoveVariableDeclarationNearReferenceRector\MoveVariableDeclarationNearReferenceRectorTest
 */
final class MoveVariableDeclarationNearReferenceRector extends AbstractRector
{
    /**
     * @var ParentScopeFinder
     */
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
        if (! $variable instanceof Variable) {
            return null;
        }

        $variableType = $this->getStaticType($variable);

        $parentScope = $this->parentScopeFinder->find($assign);
        if ($parentScope === null) {
            return null;
        }

        $firstUsedVariable = $this->findFirstVariableUsageInScope($variable, $assign, $parentScope);
        if ($firstUsedVariable === null) {
            return null;
        }

        if ($this->shouldSkipUsedVariable($firstUsedVariable)) {
            return null;
        }

        $firstVariableUsageStatement = $firstUsedVariable->getAttribute(AttributeKey::CURRENT_STATEMENT);

        $assignStatement = $assign->getAttribute(AttributeKey::CURRENT_STATEMENT);
        $this->addNodeBeforeNode($assignStatement, $firstVariableUsageStatement);

        $this->removeNode($assignStatement);

        return $node;
    }

    /**
     * Find the first node within the same method being a usage of the assigned variable,
     * but not the original assignment itself.
     *
     * @param ClassMethod|Function_|Class_|Namespace_|Closure $parentScopeNode
     */
    private function findFirstVariableUsageInScope(
        Variable $desiredVariable,
        Assign $assign,
        Node $parentScopeNode
    ): ?Variable {
        $desiredVariableName = $this->getName($desiredVariable);

        /** @var Variable|null $foundVariable */
        $foundVariable = $this->betterNodeFinder->findFirst(
            (array) $parentScopeNode->stmts,
            function (Node $node) use ($desiredVariableName, $assign): bool {
                if (! $node instanceof Variable) {
                    return false;
                }

                if ($this->isVariableInOriginalAssign($node, $assign)) {
                    return false;
                }

                return $this->isName($node, $desiredVariableName);
            }
        );

        return $foundVariable;
    }

    private function shouldSkipUsedVariable(Variable $variable): bool
    {
        $parent = $variable->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof ArrayDimFetch) {
            return true;
        }

        $variableType = $this->getStaticType($variable);

        // possibly service of value object, that changes inner state
        return $variableType instanceof TypeWithClassName;
    }

    private function isVariableInOriginalAssign(Variable $variable, Assign $assign): bool
    {
        $parentNode = $variable->getAttribute(AttributeKey::PARENT_NODE);
        return $parentNode === $assign;
    }
}
