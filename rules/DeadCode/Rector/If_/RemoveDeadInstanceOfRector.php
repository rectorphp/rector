<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\NodeTraverser;
use PHPStan\Type\MixedType;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\If_\RemoveDeadInstanceOfRector\RemoveDeadInstanceOfRectorTest
 */
final class RemoveDeadInstanceOfRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    public function __construct(IfManipulator $ifManipulator)
    {
        $this->ifManipulator = $ifManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove dead instanceof check on type hinted variable', [new CodeSample(<<<'CODE_SAMPLE'
function run(stdClass $stdClass)
{
    if (! $stdClass instanceof stdClass) {
        return false;
    }

    return true;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function run(stdClass $stdClass)
{
    return true;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [If_::class];
    }
    /**
     * @param If_ $node
     * @return Stmt[]|null|int
     */
    public function refactor(Node $node)
    {
        if (!$this->ifManipulator->isIfWithoutElseAndElseIfs($node)) {
            return null;
        }
        if ($node->cond instanceof BooleanNot && $node->cond->expr instanceof Instanceof_) {
            return $this->refactorStmtAndInstanceof($node, $node->cond->expr);
        }
        if ($node->cond instanceof Instanceof_) {
            return $this->refactorStmtAndInstanceof($node, $node->cond);
        }
        return null;
    }
    /**
     * @return null|Stmt[]|int
     */
    private function refactorStmtAndInstanceof(If_ $if, Instanceof_ $instanceof)
    {
        if (!$instanceof->class instanceof Name) {
            return null;
        }
        // handle in another rule
        if ($this->isPropertyFetch($instanceof->expr) || $instanceof->expr instanceof CallLike) {
            return null;
        }
        $classType = $this->nodeTypeResolver->getType($instanceof->class);
        $exprType = $this->nodeTypeResolver->getType($instanceof->expr);
        $isSameStaticTypeOrSubtype = $classType->equals($exprType) || $classType->isSuperTypeOf($exprType)->yes();
        if (!$isSameStaticTypeOrSubtype) {
            return null;
        }
        if ($this->shouldSkipFromNotTypedParam($instanceof)) {
            return null;
        }
        if ($instanceof->expr instanceof Assign) {
            $assignExpression = new Expression($instanceof->expr);
            return \array_merge([$assignExpression], $if->stmts);
        }
        if ($if->cond !== $instanceof) {
            return NodeTraverser::REMOVE_NODE;
        }
        if ($if->stmts === []) {
            return NodeTraverser::REMOVE_NODE;
        }
        // unwrap stmts
        return $if->stmts;
    }
    private function shouldSkipFromNotTypedParam(Instanceof_ $instanceof) : bool
    {
        $nativeParamType = $this->nodeTypeResolver->getNativeType($instanceof->expr);
        return $nativeParamType instanceof MixedType;
    }
    private function isPropertyFetch(Expr $expr) : bool
    {
        if ($expr instanceof PropertyFetch) {
            return \true;
        }
        return $expr instanceof StaticPropertyFetch;
    }
}
