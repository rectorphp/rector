<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeFinder\NextVariableUsageNodeFinder;
use Rector\DeadCode\NodeFinder\PreviousVariableAssignNodeFinder;
use Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use Rector\NodeNestingScope\ScopeNestingComparator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\Assign\RemoveUnusedAssignVariableRector\RemoveUnusedAssignVariableRectorTest
 */
final class RemoveUnusedAssignVariableRector extends AbstractRector
{
    /**
     * @var SideEffectNodeDetector
     */
    private $sideEffectNodeDetector;

    /**
     * @var PreviousVariableAssignNodeFinder
     */
    private $previousVariableAssignNodeFinder;

    /**
     * @var ScopeNestingComparator
     */
    private $scopeNestingComparator;

    /**
     * @var NextVariableUsageNodeFinder
     */
    private $nextVariableUsageNodeFinder;

    public function __construct(
        NextVariableUsageNodeFinder $nextVariableUsageNodeFinder,
        PreviousVariableAssignNodeFinder $previousVariableAssignNodeFinder,
        ScopeNestingComparator $scopeNestingComparator,
        SideEffectNodeDetector $sideEffectNodeDetector
    ) {
        $this->sideEffectNodeDetector = $sideEffectNodeDetector;
        $this->previousVariableAssignNodeFinder = $previousVariableAssignNodeFinder;
        $this->scopeNestingComparator = $scopeNestingComparator;
        $this->nextVariableUsageNodeFinder = $nextVariableUsageNodeFinder;
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove assigned unused variable', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = $this->process();
    }

    public function process()
    {
        // something going on
        return 5;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->process();
    }

    public function process()
    {
        // something going on
        return 5;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipAssign($node)) {
            return null;
        }

        if ($this->isVariableTypeInScope($node) && ! $this->isPreviousVariablePartOfOverridingAssign($node)) {
            return null;
        }

        // is scalar assign? remove whole
        if (! $this->sideEffectNodeDetector->detect($node->expr)) {
            $this->removeNode($node);
            return null;
        }

        return $node->expr;
    }

    private function shouldSkipAssign(Assign $assign): bool
    {
        if (! $assign->var instanceof Variable) {
            return true;
        }

        // unable to resolve name
        $variableName = $this->getName($assign->var);
        if ($variableName === null) {
            return true;
        }

        if ($this->isNestedAssign($assign)) {
            return true;
        }

        $nextUsedVariable = $this->nextVariableUsageNodeFinder->find($assign);
        return $nextUsedVariable !== null;
    }

    private function isVariableTypeInScope(Assign $assign): bool
    {
        $scope = $assign->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return false;
        }

        /** @var string $variableName */
        $variableName = $this->getName($assign->var);

        return ! $scope->hasVariableType($variableName)
            ->no();
    }

    private function isPreviousVariablePartOfOverridingAssign(Assign $assign): bool
    {
        // is previous variable node as part of assign?
        $previousVariableAssign = $this->previousVariableAssignNodeFinder->find($assign);
        if (! $previousVariableAssign instanceof Node) {
            return false;
        }

        return $this->scopeNestingComparator->areScopeNestingEqual($assign, $previousVariableAssign);
    }

    /**
     * Nested assign, e.g "$oldValues = <$values> = 5;"
     */
    private function isNestedAssign(Assign $assign): bool
    {
        $parent = $assign->getAttribute(AttributeKey::PARENT_NODE);
        return $parent instanceof Assign;
    }
}
