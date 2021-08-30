<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use PHPStan\Analyser\Scope;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeAnalyzer\ExprUsedInNextNodeAnalyzer;
use Rector\DeadCode\NodeFinder\NextVariableUsageNodeFinder;
use Rector\DeadCode\NodeFinder\PreviousVariableAssignNodeFinder;
use Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use Rector\NodeNestingScope\ScopeNestingComparator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedAssignVariableRector\RemoveUnusedAssignVariableRectorTest
 */
final class RemoveUnusedAssignVariableRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\DeadCode\NodeFinder\NextVariableUsageNodeFinder
     */
    private $nextVariableUsageNodeFinder;
    /**
     * @var \Rector\DeadCode\NodeFinder\PreviousVariableAssignNodeFinder
     */
    private $previousVariableAssignNodeFinder;
    /**
     * @var \Rector\NodeNestingScope\ScopeNestingComparator
     */
    private $scopeNestingComparator;
    /**
     * @var \Rector\DeadCode\SideEffect\SideEffectNodeDetector
     */
    private $sideEffectNodeDetector;
    /**
     * @var \Rector\DeadCode\NodeAnalyzer\ExprUsedInNextNodeAnalyzer
     */
    private $exprUsedInNextNodeAnalyzer;
    public function __construct(\Rector\DeadCode\NodeFinder\NextVariableUsageNodeFinder $nextVariableUsageNodeFinder, \Rector\DeadCode\NodeFinder\PreviousVariableAssignNodeFinder $previousVariableAssignNodeFinder, \Rector\NodeNestingScope\ScopeNestingComparator $scopeNestingComparator, \Rector\DeadCode\SideEffect\SideEffectNodeDetector $sideEffectNodeDetector, \Rector\DeadCode\NodeAnalyzer\ExprUsedInNextNodeAnalyzer $exprUsedInNextNodeAnalyzer)
    {
        $this->nextVariableUsageNodeFinder = $nextVariableUsageNodeFinder;
        $this->previousVariableAssignNodeFinder = $previousVariableAssignNodeFinder;
        $this->scopeNestingComparator = $scopeNestingComparator;
        $this->sideEffectNodeDetector = $sideEffectNodeDetector;
        $this->exprUsedInNextNodeAnalyzer = $exprUsedInNextNodeAnalyzer;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Assign::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove assigned unused variable', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @param Assign $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkipAssign($node)) {
            return null;
        }
        if (!$this->isPreviousVariablePartOfOverridingAssign($node) && ($this->isVariableTypeInScope($node) || $this->exprUsedInNextNodeAnalyzer->isUsed($node->var))) {
            return null;
        }
        // @see https://github.com/rectorphp/rector/issues/6655
        // verify current statement is a Node before removing or use its Assign Expr
        $currentStatement = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
        if (!$currentStatement instanceof \PhpParser\Node) {
            return null;
        }
        // is scalar assign? remove whole
        if (!$this->sideEffectNodeDetector->detect($node->expr)) {
            $this->removeNode($node);
            return null;
        }
        return $node->expr;
    }
    private function shouldSkipAssign(\PhpParser\Node\Expr\Assign $assign) : bool
    {
        $variable = $assign->var;
        if (!$variable instanceof \PhpParser\Node\Expr\Variable) {
            return \true;
        }
        // unable to resolve name
        $variableName = $this->getName($variable);
        if ($variableName === null) {
            return \true;
        }
        if ($this->isNestedAssign($assign)) {
            return \true;
        }
        $parentIf = $this->betterNodeFinder->findParentType($assign, \PhpParser\Node\Stmt\If_::class);
        if (!$parentIf instanceof \PhpParser\Node\Stmt\If_) {
            return (bool) $this->nextVariableUsageNodeFinder->find($assign);
        }
        if (!$parentIf->else instanceof \PhpParser\Node\Stmt\Else_) {
            return (bool) $this->nextVariableUsageNodeFinder->find($assign);
        }
        return (bool) $this->betterNodeFinder->findFirstNext($parentIf, function (\PhpParser\Node $node) use($variable) : bool {
            return $this->nodeComparator->areNodesEqual($node, $variable);
        });
    }
    private function isVariableTypeInScope(\PhpParser\Node\Expr\Assign $assign) : bool
    {
        $scope = $assign->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        /** @var string $variableName */
        $variableName = $this->getName($assign->var);
        return !$scope->hasVariableType($variableName)->no();
    }
    private function isPreviousVariablePartOfOverridingAssign(\PhpParser\Node\Expr\Assign $assign) : bool
    {
        // is previous variable node as part of assign?
        $previousVariableAssign = $this->previousVariableAssignNodeFinder->find($assign);
        if (!$previousVariableAssign instanceof \PhpParser\Node) {
            return \false;
        }
        return $this->scopeNestingComparator->areScopeNestingEqual($assign, $previousVariableAssign);
    }
    /**
     * Nested assign, e.g "$oldValues = <$values> = 5;"
     */
    private function isNestedAssign(\PhpParser\Node\Expr\Assign $assign) : bool
    {
        $parent = $assign->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        return $parent instanceof \PhpParser\Node\Expr\Assign;
    }
}
