<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\NodeManipulator\NullsafeManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/nullsafe_operator
 * @see \Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\NullsafeOperatorRectorTest
 */
final class NullsafeOperatorRector extends AbstractRector
{
    public function __construct(
        private IfManipulator $ifManipulator,
        private NullsafeManipulator $nullsafeManipulator
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change if null check with nullsafe operator ?-> with full short circuiting',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($someObject)
    {
        $someObject2 = $someObject->mayFail1();
        if ($someObject2 === null) {
            return null;
        }

        return $someObject2->mayFail2();
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($someObject)
    {
        return $someObject->mayFail1()?->mayFail2();
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [If_::class, Ternary::class];
    }

    /**
     * @param If_|Ternary $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof If_) {
            return $this->handleIfNode($node);
        }

        return $this->handleTernaryNode($node);
    }

    private function handleIfNode(If_ $if): ?Node
    {
        $processNullSafeOperator = $this->processNullSafeOperatorIdentical($if);
        if ($processNullSafeOperator !== null) {
            /** @var Expression $prevNode */
            $prevNode = $if->getAttribute(AttributeKey::PREVIOUS_NODE);
            $this->removeNode($prevNode);

            return $processNullSafeOperator;
        }

        return $this->processNullSafeOperatorNotIdentical($if);
    }

    private function processNullSafeOperatorIdentical(If_ $if, bool $isStartIf = true): ?Node
    {
        $comparedNode = $this->ifManipulator->matchIfValueReturnValue($if);
        if (! $comparedNode instanceof Expr) {
            return null;
        }

        $prevNode = $if->getAttribute(AttributeKey::PREVIOUS_NODE);
        $nextNode = $if->getAttribute(AttributeKey::NEXT_NODE);

        if (! $nextNode instanceof Node) {
            return null;
        }

        if (! $prevNode instanceof Expression) {
            return null;
        }

        $prevExpr = $prevNode->expr;
        if (! $prevExpr instanceof Assign) {
            return null;
        }

        if (! $this->ifManipulator->isIfCondUsingAssignIdenticalVariable($if, $prevExpr)) {
            return null;
        }

        if ($this->hasIndirectUsageOnNextOfIf($prevExpr->var, $nextNode)) {
            return null;
        }

        return $this->processAssign($prevExpr, $prevNode, $nextNode, $isStartIf);
    }

    private function hasIndirectUsageOnNextOfIf(Expr $expr, Node $nextNode): bool
    {
        if (! $nextNode instanceof Return_ && ! $nextNode instanceof Expression) {
            return false;
        }

        if ($nextNode->expr instanceof PropertyFetch) {
            return ! $this->nodeComparator->areNodesEqual($expr, $nextNode->expr->var);
        }

        if ($nextNode->expr instanceof MethodCall) {
            return ! $this->nodeComparator->areNodesEqual($expr, $nextNode->expr->var);
        }

        return false;
    }

    private function processNullSafeOperatorNotIdentical(If_ $if, ?Expr $expr = null): ?Node
    {
        $assign = $this->ifManipulator->matchIfNotNullNextAssignment($if);
        if (! $assign instanceof Assign) {
            return null;
        }

        $assignExpr = $assign->expr;
        if ($this->ifManipulator->isIfCondUsingAssignNotIdenticalVariable($if, $assignExpr)) {
            return null;
        }

        $expression = $assign->getAttribute(AttributeKey::PARENT_NODE);
        if (! $expression instanceof Expression) {
            return null;
        }

        $nextNode = $expression->getAttribute(AttributeKey::NEXT_NODE);
        $nullSafe = $this->nullsafeManipulator->processNullSafeExpr($assignExpr);
        if ($nullSafe === null) {
            return null;
        }

        if ($expr !== null) {
            /** @var Identifier $nullSafeIdentifier */
            $nullSafeIdentifier = $nullSafe->name;
            /** @var NullsafeMethodCall|NullsafePropertyFetch $nullSafe */
            $nullSafe = $this->nullsafeManipulator->processNullSafeExprResult($expr, $nullSafeIdentifier);
        }

        $nextOfNextNode = $this->processIfMayInNextNode($nextNode);
        if ($nextOfNextNode !== null) {
            return $nextOfNextNode;
        }

        if (! $nextNode instanceof If_) {
            $nullSafe = $this->verifyDefaultValueInElse($if, $nullSafe, $assign);
            if ($nullSafe === null) {
                return null;
            }

            return new Assign($assign->var, $nullSafe);
        }

        return $this->processNullSafeOperatorNotIdentical($nextNode, $nullSafe);
    }

    private function verifyDefaultValueInElse(
        If_ $if,
        NullsafeMethodCall|NullsafePropertyFetch $nullSafe,
        Assign $assign
    ): NullsafeMethodCall|NullsafePropertyFetch|Coalesce|null {
        if (! $if->else instanceof Else_) {
            return $nullSafe;
        }

        if (count($if->else->stmts) !== 1) {
            return null;
        }

        $expression = $if->else->stmts[0];
        if (! $expression instanceof Expression) {
            return null;
        }

        $expressionAssign = $expression->expr;
        if (! $expressionAssign instanceof Assign) {
            return null;
        }

        if (! $this->nodeComparator->areNodesEqual($expressionAssign->var, $assign->var)) {
            return null;
        }

        if ($this->valueResolver->isNull($expressionAssign->expr)) {
            return $nullSafe;
        }

        return new Coalesce($nullSafe, $expressionAssign->expr);
    }

    private function processAssign(Assign $assign, Expression $prevExpression, Node $nextNode, bool $isStartIf): ?Node
    {
        if ($this->shouldProcessAssignInCurrentNode($assign, $nextNode)) {
            return $this->processAssignInCurrentNode($assign, $prevExpression, $nextNode, $isStartIf);
        }

        return $this->processAssignMayInNextNode($nextNode);
    }

    private function shouldProcessAssignInCurrentNode(Assign $assign, Node $nextNode): bool
    {
        if (! $nextNode instanceof Return_ && ! $nextNode instanceof Expression) {
            return false;
        }

        if ($nextNode->expr instanceof Assign) {
            return false;
        }

        if (! $nextNode->expr instanceof Node) {
            return false;
        }

        if (! $this->isMethodCallOrPropertyFetch($assign->expr)) {
            return ! $this->valueResolver->isNull($nextNode->expr);
        }

        /** @var MethodCall|PropertyFetch $expr */
        $expr = $assign->expr;
        if (! $this->isMethodCallOrPropertyFetch($expr->var)) {
            return ! $this->valueResolver->isNull($expr);
        }

        if (! $this->isMethodCallOrPropertyFetch($nextNode->expr)) {
            return ! $this->valueResolver->isNull($nextNode->expr);
        }

        return false;
    }

    private function processIfMayInNextNode(?Node $nextNode = null): ?Node
    {
        if (! $nextNode instanceof Node) {
            return null;
        }

        $nextOfNextNode = $nextNode->getAttribute(AttributeKey::NEXT_NODE);
        while ($nextOfNextNode) {
            if ($nextOfNextNode instanceof If_) {
                /** @var If_ $beforeIf */
                $beforeIf = $nextOfNextNode->getAttribute(AttributeKey::PARENT_NODE);
                $nullSafe = $this->processNullSafeOperatorNotIdentical($nextOfNextNode);
                if (! $nullSafe instanceof NullsafeMethodCall && ! $nullSafe instanceof PropertyFetch) {
                    return $beforeIf;
                }

                $beforeIf->stmts[count($beforeIf->stmts) - 1] = new Expression($nullSafe);
                return $beforeIf;
            }

            $nextOfNextNode = $nextOfNextNode->getAttribute(AttributeKey::NEXT_NODE);
        }

        return null;
    }

    private function processAssignInCurrentNode(
        Assign $assign,
        Expression $expression,
        Node $nextNode,
        bool $isStartIf
    ): ?Node {
        $assignNullSafe = $isStartIf
            ? $assign->expr
            : $this->nullsafeManipulator->processNullSafeExpr($assign->expr);
        $nullSafe = $this->nullsafeManipulator->processNullSafeExprResult($assignNullSafe, $nextNode->expr->name);

        $prevAssign = $expression->getAttribute(AttributeKey::PREVIOUS_NODE);
        if ($prevAssign instanceof If_) {
            $nullSafe = $this->getNullSafeOnPrevAssignIsIf($prevAssign, $nextNode, $nullSafe);
        }

        $this->removeNode($nextNode);

        if ($nextNode instanceof Return_) {
            $nextNode->expr = $nullSafe;
            return $nextNode;
        }

        return $nullSafe;
    }

    private function processAssignMayInNextNode(Node $nextNode): ?Node
    {
        if (! $nextNode instanceof Expression) {
            return null;
        }

        if (! $nextNode->expr instanceof Assign) {
            return null;
        }

        $mayNextIf = $nextNode->getAttribute(AttributeKey::NEXT_NODE);
        if (! $mayNextIf instanceof If_) {
            return null;
        }

        if ($this->ifManipulator->isIfCondUsingAssignIdenticalVariable($mayNextIf, $nextNode->expr)) {
            return $this->processNullSafeOperatorIdentical($mayNextIf, false);
        }

        return null;
    }

    private function getNullSafeOnPrevAssignIsIf(If_ $if, Node $nextNode, ?Expr $expr): ?Expr
    {
        $prevIf = $if->getAttribute(AttributeKey::PREVIOUS_NODE);
        if (! $prevIf instanceof Expression) {
            return $expr;
        }

        if (! $this->ifManipulator->isIfCondUsingAssignIdenticalVariable($if, $prevIf->expr)) {
            return $expr;
        }

        $start = $prevIf;

        while ($prevIf instanceof Expression) {
            $expressionNode = $prevIf->expr;
            if (! $expressionNode instanceof Assign) {
                return null;
            }

            $expr = $this->nullsafeManipulator->processNullSafeExpr($expressionNode->expr);

            /** @var Node $prevPrevIf */
            $prevPrevIf = $prevIf->getAttribute(AttributeKey::PREVIOUS_NODE);
            /** @var Node $prevPrevPrevIf */
            $prevPrevPrevIf = $prevPrevIf->getAttribute(AttributeKey::PREVIOUS_NODE);

            if (! $prevPrevPrevIf instanceof Expression && $prevPrevPrevIf !== null) {
                $start = $this->getPreviousIf($prevPrevPrevIf);
                break;
            }

            $prevIf = $prevPrevPrevIf;
        }

        if (! $expr instanceof NullsafeMethodCall && ! $expr instanceof NullsafePropertyFetch) {
            return $expr;
        }

        /** @var Expr $expr */
        $expr = $expr->var->getAttribute(AttributeKey::PARENT_NODE);
        $expr = $this->getNullSafeAfterStartUntilBeforeEnd($start, $expr);

        return $this->nullsafeManipulator->processNullSafeExprResult($expr, $nextNode->expr->name);
    }

    private function getPreviousIf(Node $node): ?Node
    {
        /** @var If_ $if */
        $if = $node->getAttribute(AttributeKey::NEXT_NODE);

        /** @var Expression $expression */
        $expression = $if->getAttribute(AttributeKey::NEXT_NODE);

        /** @var Expression $nextExpression */
        $nextExpression = $expression->getAttribute(AttributeKey::NEXT_NODE);

        return $nextExpression->getAttribute(AttributeKey::NEXT_NODE);
    }

    private function getNullSafeAfterStartUntilBeforeEnd(?Node $node, ?Expr $expr): ?Expr
    {
        while ($node) {
            $expr = $this->nullsafeManipulator->processNullSafeExprResult($expr, $node->expr->expr->name);

            $node = $node->getAttribute(AttributeKey::NEXT_NODE);
            while ($node) {
                /** @var If_ $if */
                $if = $node->getAttribute(AttributeKey::NEXT_NODE);
                if ($node instanceof Expression && $this->ifManipulator->isIfCondUsingAssignIdenticalVariable(
                    $if,
                    $node->expr
                )) {
                    break;
                }

                $node = $node->getAttribute(AttributeKey::NEXT_NODE);
            }
        }

        return $expr;
    }

    private function handleTernaryNode(Ternary $ternary): ?Node
    {
        if ($this->shouldSkipTernary($ternary)) {
            return null;
        }

        $nullSafeElse = $this->nullsafeManipulator->processNullSafeExpr($ternary->else);

        if ($nullSafeElse !== null) {
            return $nullSafeElse;
        }

        if ($ternary->if === null) {
            return null;
        }

        return $this->nullsafeManipulator->processNullSafeExpr($ternary->if);
    }

    private function shouldSkipTernary(Ternary $ternary): bool
    {
        if (! $this->canTernaryReturnNull($ternary)) {
            return true;
        }

        if (! $ternary->cond instanceof Identical && ! $ternary->cond instanceof NotIdentical) {
            return true;
        }

        if (! $this->hasNullComparison($ternary->cond)) {
            return true;
        }

        return $this->hasIndirectUsageOnElse($ternary->cond, $ternary->if, $ternary->else);
    }

    private function hasIndirectUsageOnElse(Identical|NotIdentical $cond, ?Expr $if, Expr $expr): bool
    {
        $left = $cond->left;
        $right = $cond->right;

        $object = $this->valueResolver->isNull($left)
            ? $right
            : $left;

        if ($this->valueResolver->isNull($expr)) {
            if ($this->isMethodCallOrPropertyFetch($if)) {
                /** @var MethodCall|PropertyFetch $if */
                return ! $this->nodeComparator->areNodesEqual($if->var, $object);
            }

            return false;
        }

        if ($this->isMethodCallOrPropertyFetch($expr)) {
            /** @var MethodCall|PropertyFetch $expr */
            return ! $this->nodeComparator->areNodesEqual($expr->var, $object);
        }

        return false;
    }

    private function isMethodCallOrPropertyFetch(?Expr $expr): bool
    {
        return $expr instanceof MethodCall || $expr instanceof PropertyFetch;
    }

    private function hasNullComparison(NotIdentical|Identical $check): bool
    {
        if ($this->valueResolver->isNull($check->left)) {
            return true;
        }

        return $this->valueResolver->isNull($check->right);
    }

    private function canTernaryReturnNull(Ternary $ternary): bool
    {
        if ($this->valueResolver->isNull($ternary->else)) {
            return true;
        }

        if ($ternary->if === null) {
            // $foo === null ?: 'xx' returns true if $foo is null
            // therefore it does not return null in case of the elvis operator
            return false;
        }

        return $this->valueResolver->isNull($ternary->if);
    }
}
