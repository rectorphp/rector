<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Assign\RemoveDoubleAssignRector\RemoveDoubleAssignRectorTest
 */
final class RemoveDoubleAssignRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DeadCode\SideEffect\SideEffectNodeDetector
     */
    private $sideEffectNodeDetector;
    public function __construct(\Rector\DeadCode\SideEffect\SideEffectNodeDetector $sideEffectNodeDetector)
    {
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
        return [\PhpParser\Node\Stmt\Foreach_::class, \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace::class, \PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Expr\Closure::class, \PhpParser\Node\Stmt\If_::class, \PhpParser\Node\Stmt\Namespace_::class];
    }
    /**
     * @param Foreach_|FileWithoutNamespace|If_|Namespace_|ClassMethod|Function_|Closure $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $stmts = $node->stmts;
        if ($stmts === null) {
            return null;
        }
        $hasChanged = \false;
        $previousStmt = null;
        foreach ($stmts as $key => $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\Expression) {
                $previousStmt = $stmt;
                continue;
            }
            $expr = $stmt->expr;
            if (!$expr instanceof \PhpParser\Node\Expr\Assign) {
                $previousStmt = $stmt;
                continue;
            }
            if (!$expr->var instanceof \PhpParser\Node\Expr\Variable && !$expr->var instanceof \PhpParser\Node\Expr\PropertyFetch && !$expr->var instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
                $previousStmt = $stmt;
                continue;
            }
            if (!$previousStmt instanceof \PhpParser\Node\Stmt\Expression) {
                $previousStmt = $stmt;
                continue;
            }
            if (!$previousStmt->expr instanceof \PhpParser\Node\Expr\Assign) {
                $previousStmt = $stmt;
                continue;
            }
            $previousAssign = $previousStmt->expr;
            if (!$this->nodeComparator->areNodesEqual($previousAssign->var, $expr->var)) {
                $previousStmt = $stmt;
                continue;
            }
            // early check self referencing, ensure that variable not re-used
            if ($this->isSelfReferencing($expr)) {
                $previousStmt = $stmt;
                continue;
            }
            // detect call expression has side effect
            // no calls on right, could hide e.g. array_pop()|array_shift()
            if ($this->sideEffectNodeDetector->detectCallExpr($previousAssign->expr)) {
                $previousStmt = $stmt;
                continue;
            }
            // remove previous stmt, not current one as the current one is the override
            unset($stmts[$key - 1]);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        \ksort($stmts);
        $node->stmts = $stmts;
        return $node;
    }
    private function isSelfReferencing(\PhpParser\Node\Expr\Assign $assign) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($assign->expr, function (\PhpParser\Node $subNode) use($assign) : bool {
            return $this->nodeComparator->areNodesEqual($assign->var, $subNode);
        });
    }
}
