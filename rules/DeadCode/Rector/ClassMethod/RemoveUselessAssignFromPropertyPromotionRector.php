<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessAssignFromPropertyPromotionRector\RemoveUselessAssignFromPropertyPromotionRectorTest
 */
final class RemoveUselessAssignFromPropertyPromotionRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove useless re-assign from property promotion', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(private \stdClass $std)
    {
    	$this->std = $std;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(private \stdClass $std)
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, MethodName::CONSTRUCT)) {
            return null;
        }
        if ($node->stmts === null || $node->stmts == []) {
            return null;
        }
        $variableNames = [];
        foreach ($node->params as $param) {
            if (!$param->isPromoted()) {
                continue;
            }
            // re-assign will cause error on the first place, no need to collect names
            // on readonly property promotion
            if ($param->isReadonly()) {
                continue;
            }
            $variableNames[] = (string) $this->getName($param->var);
        }
        if ($variableNames === []) {
            return null;
        }
        $removeStmtKeys = [];
        foreach ($node->stmts as $key => $stmt) {
            // has non direct expression with assign, skip
            if (!$stmt instanceof Expression || !$stmt->expr instanceof Assign) {
                return null;
            }
            /** @var Assign $assign */
            $assign = $stmt->expr;
            // has non property fetches assignments, skip
            if (!$assign->var instanceof PropertyFetch) {
                return null;
            }
            // collect first, ensure not removed too early on next non property fetch assignment
            // which may have side effect
            if ($assign->var->var instanceof Variable && $this->isName($assign->var->var, 'this') && $this->isNames($assign->var->name, $variableNames) && $assign->expr instanceof Variable && $this->isName($assign->expr, (string) $this->getName($assign->var->name))) {
                $removeStmtKeys[] = $key;
                continue;
            }
            // early return, if not all are property fetches from $this and its param
            return null;
        }
        // empty data? nothing to remove
        if ($removeStmtKeys === []) {
            return null;
        }
        foreach ($removeStmtKeys as $removeStmtKey) {
            unset($node->stmts[$removeStmtKey]);
        }
        return $node;
    }
}
