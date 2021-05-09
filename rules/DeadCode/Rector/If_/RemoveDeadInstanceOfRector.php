<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Stmt\If_;
use PHPStan\Analyser\Scope;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\If_\RemoveDeadInstanceOfRector\RemoveDeadInstanceOfRectorTest
 */
final class RemoveDeadInstanceOfRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var IfManipulator
     */
    private $ifManipulator;
    public function __construct(\Rector\Core\NodeManipulator\IfManipulator $ifManipulator)
    {
        $this->ifManipulator = $ifManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove dead instanceof check on type hinted variable', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function go(stdClass $stdClass)
    {
        if (! $stdClass instanceof stdClass) {
            return false;
        }

        return true;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function go(stdClass $stdClass)
    {
        return true;
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
        return [\PhpParser\Node\Stmt\If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        // a trait
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        if (!$this->ifManipulator->isIfWithoutElseAndElseIfs($node)) {
            return null;
        }
        if ($node->cond instanceof \PhpParser\Node\Expr\BooleanNot && $node->cond->expr instanceof \PhpParser\Node\Expr\Instanceof_) {
            return $this->processMayDeadInstanceOf($node, $node->cond->expr);
        }
        if ($node->cond instanceof \PhpParser\Node\Expr\Instanceof_) {
            return $this->processMayDeadInstanceOf($node, $node->cond);
        }
        return $node;
    }
    private function processMayDeadInstanceOf(\PhpParser\Node\Stmt\If_ $if, \PhpParser\Node\Expr\Instanceof_ $instanceof) : ?\PhpParser\Node\Stmt\If_
    {
        $classType = $this->nodeTypeResolver->resolve($instanceof->class);
        $exprType = $this->nodeTypeResolver->resolve($instanceof->expr);
        $isSameStaticTypeOrSubtype = $classType->equals($exprType) || $classType->isSuperTypeOf($exprType)->yes();
        if (!$isSameStaticTypeOrSubtype) {
            return null;
        }
        if ($if->cond === $instanceof) {
            $this->unwrapStmts($if->stmts, $if);
            $this->removeNode($if);
            return null;
        }
        $this->removeNode($if);
        return $if;
    }
}
