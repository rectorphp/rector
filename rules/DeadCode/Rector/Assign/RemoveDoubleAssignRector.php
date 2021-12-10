<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use Rector\NodeNestingScope\ScopeNestingComparator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Assign\RemoveDoubleAssignRector\RemoveDoubleAssignRectorTest
 */
final class RemoveDoubleAssignRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeNestingScope\ScopeNestingComparator
     */
    private $scopeNestingComparator;
    /**
     * @readonly
     * @var \Rector\DeadCode\SideEffect\SideEffectNodeDetector
     */
    private $sideEffectNodeDetector;
    public function __construct(\Rector\NodeNestingScope\ScopeNestingComparator $scopeNestingComparator, \Rector\DeadCode\SideEffect\SideEffectNodeDetector $sideEffectNodeDetector)
    {
        $this->scopeNestingComparator = $scopeNestingComparator;
        $this->sideEffectNodeDetector = $sideEffectNodeDetector;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Simplify useless double assigns', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$value = 1;
$value = 1;
CODE_SAMPLE
, '$value = 1;')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node->var instanceof \PhpParser\Node\Expr\Variable && !$node->var instanceof \PhpParser\Node\Expr\PropertyFetch && !$node->var instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
            return null;
        }
        $previousStatement = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PREVIOUS_STATEMENT);
        if (!$previousStatement instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        if (!$previousStatement->expr instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($previousStatement->expr->var, $node->var)) {
            return null;
        }
        // early check self referencing, ensure that variable not re-used
        if ($this->isSelfReferencing($node)) {
            return null;
        }
        // detect call expression has side effect
        if ($this->sideEffectNodeDetector->detectCallExpr($previousStatement->expr->expr)) {
            return null;
        }
        // check scoping variable
        if ($this->shouldSkipForDifferentScope($node, $previousStatement)) {
            return null;
        }
        // no calls on right, could hide e.g. array_pop()|array_shift()
        $this->removeNode($previousStatement);
        return $node;
    }
    private function shouldSkipForDifferentScope(\PhpParser\Node\Expr\Assign $assign, \PhpParser\Node\Stmt\Expression $expression) : bool
    {
        if (!$this->areInSameClassMethod($assign, $expression)) {
            return \true;
        }
        if (!$this->scopeNestingComparator->areScopeNestingEqual($assign, $expression)) {
            return \true;
        }
        return (bool) $this->betterNodeFinder->findParentByTypes($assign, [\PhpParser\Node\Expr\Ternary::class, \PhpParser\Node\Expr\BinaryOp\Coalesce::class]);
    }
    private function isSelfReferencing(\PhpParser\Node\Expr\Assign $assign) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($assign->expr, function (\PhpParser\Node $subNode) use($assign) : bool {
            return $this->nodeComparator->areNodesEqual($assign->var, $subNode);
        });
    }
    private function areInSameClassMethod(\PhpParser\Node\Expr\Assign $assign, \PhpParser\Node\Stmt\Expression $previousExpression) : bool
    {
        $assignClassMethod = $this->betterNodeFinder->findParentType($assign, \PhpParser\Node\Stmt\ClassMethod::class);
        $previousExpressionClassMethod = $this->betterNodeFinder->findParentType($previousExpression, \PhpParser\Node\Stmt\ClassMethod::class);
        return $this->nodeComparator->areNodesEqual($assignClassMethod, $previousExpressionClassMethod);
    }
}
