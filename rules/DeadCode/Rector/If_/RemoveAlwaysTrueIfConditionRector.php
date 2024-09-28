<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use PhpParser\NodeTraverser;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\IntersectionType;
use Rector\DeadCode\NodeAnalyzer\SafeLeftTypeBooleanAndOrAnalyzer;
use Rector\NodeAnalyzer\ExprAnalyzer;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector\RemoveAlwaysTrueIfConditionRectorTest
 */
final class RemoveAlwaysTrueIfConditionRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\SafeLeftTypeBooleanAndOrAnalyzer
     */
    private $safeLeftTypeBooleanAndOrAnalyzer;
    public function __construct(ExprAnalyzer $exprAnalyzer, BetterNodeFinder $betterNodeFinder, SafeLeftTypeBooleanAndOrAnalyzer $safeLeftTypeBooleanAndOrAnalyzer)
    {
        $this->exprAnalyzer = $exprAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->safeLeftTypeBooleanAndOrAnalyzer = $safeLeftTypeBooleanAndOrAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove if condition that is always true', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function go()
    {
        if (1 === 1) {
            return 'yes';
        }

        return 'no';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function go()
    {
        return 'yes';

        return 'no';
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
        return [If_::class];
    }
    /**
     * @param If_ $node
     * @return int|null|Stmt[]|If_
     */
    public function refactor(Node $node)
    {
        if ($node->cond instanceof BooleanAnd) {
            return $this->refactorIfWithBooleanAnd($node);
        }
        if ($node->else instanceof Else_) {
            return null;
        }
        // just one if
        if ($node->elseifs !== []) {
            return null;
        }
        $conditionStaticType = $this->getType($node->cond);
        if (!$conditionStaticType instanceof ConstantBooleanType) {
            return null;
        }
        if (!$conditionStaticType->getValue()) {
            return null;
        }
        if ($this->shouldSkipExpr($node->cond)) {
            return null;
        }
        if ($this->shouldSkipFromVariable($node->cond)) {
            return null;
        }
        $hasAssign = (bool) $this->betterNodeFinder->findFirstInstanceOf($node->cond, Assign::class);
        if ($hasAssign) {
            return null;
        }
        if ($node->stmts === []) {
            return NodeTraverser::REMOVE_NODE;
        }
        return $node->stmts;
    }
    private function shouldSkipFromVariable(Expr $expr) : bool
    {
        /** @var Variable[] $variables */
        $variables = $this->betterNodeFinder->findInstancesOf($expr, [Variable::class]);
        foreach ($variables as $variable) {
            if ($this->exprAnalyzer->isNonTypedFromParam($variable)) {
                return \true;
            }
            $type = $this->getType($variable);
            if ($type instanceof IntersectionType) {
                foreach ($type->getTypes() as $subType) {
                    if ($subType instanceof ArrayType) {
                        return \true;
                    }
                }
            }
        }
        return \false;
    }
    private function shouldSkipExpr(Expr $expr) : bool
    {
        return (bool) $this->betterNodeFinder->findInstancesOf($expr, [PropertyFetch::class, StaticPropertyFetch::class, ArrayDimFetch::class, MethodCall::class, StaticCall::class]);
    }
    private function refactorIfWithBooleanAnd(If_ $if) : ?If_
    {
        if (!$if->cond instanceof BooleanAnd) {
            return null;
        }
        $booleanAnd = $if->cond;
        $leftType = $this->getType($booleanAnd->left);
        if (!$leftType instanceof ConstantBooleanType) {
            return null;
        }
        if (!$leftType->getValue()) {
            return null;
        }
        if (!$this->safeLeftTypeBooleanAndOrAnalyzer->isSafe($booleanAnd)) {
            return null;
        }
        $if->cond = $booleanAnd->right;
        return $if;
    }
}
