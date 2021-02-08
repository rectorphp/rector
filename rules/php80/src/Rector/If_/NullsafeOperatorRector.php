<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
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
 * @see https://wiki.php.net/rfc/nullsafe_operator
 * @see \Rector\Php80\Tests\Rector\If_\NullsafeOperatorRector\NullsafeOperatorRectorTest
 */
final class NullsafeOperatorRector extends AbstractRector
{
    /**
     * @var string
     */
    private const NAME = 'name';

    /**
     * @var IfManipulator
     */
    private $ifManipulator;

    /**
     * @var NullsafeManipulator
     */
    private $nullsafeManipulator;

    public function __construct(IfManipulator $ifManipulator, NullsafeManipulator $nullsafeManipulator)
    {
        $this->ifManipulator = $ifManipulator;
        $this->nullsafeManipulator = $nullsafeManipulator;
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

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $processNullSafeOperator = $this->processNullSafeOperatorIdentical($node);
        if ($processNullSafeOperator !== null) {
            /** @var Expression $prevNode */
            $prevNode = $node->getAttribute(AttributeKey::PREVIOUS_NODE);
            $this->removeNode($prevNode);

            return $processNullSafeOperator;
        }

        return $this->processNullSafeOperatorNotIdentical($node);
    }

    private function processNullSafeOperatorIdentical(If_ $if, bool $isStartIf = true): ?Node
    {
        $comparedNode = $this->ifManipulator->matchIfValueReturnValue($if);
        if (! $comparedNode instanceof \PhpParser\Node\Expr) {
            return null;
        }

        $prevNode = $if->getAttribute(AttributeKey::PREVIOUS_NODE);
        $nextNode = $if->getAttribute(AttributeKey::NEXT_NODE);

        if (! $prevNode instanceof \PhpParser\Node) {
            return null;
        }

        if (! $nextNode instanceof \PhpParser\Node) {
            return null;
        }

        if (! $prevNode instanceof Expression) {
            return null;
        }

        if (! $this->ifManipulator->isIfCondUsingAssignIdenticalVariable($if, $prevNode->expr)) {
            return null;
        }

        $prevExpr = $prevNode->expr;
        if (! $prevExpr instanceof Assign) {
            return null;
        }

        return $this->processAssign($prevExpr, $prevNode, $nextNode, $isStartIf);
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

        /** @var Expression $expression */
        $expression = $assign->getAttribute(AttributeKey::PARENT_NODE);

        $nextNode = $expression->getAttribute(AttributeKey::NEXT_NODE);

        /** @var NullsafeMethodCall|NullsafePropertyFetch $nullSafe */
        $nullSafe = $this->nullsafeManipulator->processNullSafeExpr($assignExpr);
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
            return new Assign($assign->var, $nullSafe);
        }

        return $this->processNullSafeOperatorNotIdentical($nextNode, $nullSafe);
    }

    private function processAssign(Assign $assign, Expression $prevExpression, Node $nextNode, bool $isStartIf): ?Node
    {
        if ($assign instanceof Assign && property_exists(
            $assign->expr,
            self::NAME
        ) && property_exists($nextNode, 'expr') && property_exists($nextNode->expr, self::NAME)) {
            return $this->processAssignInCurrentNode($assign, $prevExpression, $nextNode, $isStartIf);
        }

        return $this->processAssignMayInNextNode($nextNode);
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
}
