<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Node\Manipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

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

    public function __construct(IfManipulator $ifManipulator)
    {
        $this->ifManipulator = $ifManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change if null check with nullsafe operator ?-> with full short circuiting', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function f($o)
    {
        $o2 = $o->mayFail1();
        if ($o2 === null) {
            return null;
        }

        return $o2->mayFail2();
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function f($o)
    {
        return $o?->mayFail1()?->mayFail2();
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
        $comparedNode = $this->ifManipulator->matchIfValueReturnValue($node);

        if ($comparedNode !== null) {
            $prevNode = $node->getAttribute(AttributeKey::PREVIOUS_NODE);
            $nextNode = $node->getAttribute(AttributeKey::NEXT_NODE);

            return $this->processNullSafeOperator($node, $prevNode, $nextNode);
        }

        return null;
    }

    private function processNullSafeOperator(If_ $if, ?Node $prevNode, ?Node $nextNode): ?Node
    {
        if ($prevNode === null || $nextNode === null) {
            return null;
        }

        while ($prevNode) {
            /** @var Assign|null $assign */
            $assign = $this->betterNodeFinder->findFirst($prevNode, function (Node $node) use ($if): bool {
                return $node instanceof Assign && $if->cond instanceof Identical && $this->getName(
                    $if->cond->left
                ) === $this->getName($node->var);
            });

            $processAssign = $this->processAssign($assign, $prevNode, $nextNode);
            if ($processAssign instanceof Node) {
                return $processAssign;
            }

            $prevNode = $prevNode->getAttribute(AttributeKey::PREVIOUS_NODE);
        }

        return null;
    }

    private function processAssign(?Assign $assign, Node $prevNode, Node $nextNode): ?Node
    {
        if ($assign instanceof Assign && property_exists(
            $assign->expr,
            self::NAME
        ) && $nextNode->expr instanceof Expr && property_exists($nextNode->expr, self::NAME)) {
            $assignNullSafe = $this->processNullSafeExpr($assign->expr);
            $nullSafe = $this->processNullSafeExprResult($assignNullSafe, $nextNode->expr->name);

            $this->removeNode($prevNode);
            $this->removeNode($nextNode);

            if ($nextNode instanceof Return_) {
                return new Return_($nullSafe);
            }

            return $nullSafe;
        }

        return null;
    }

    private function processNullSafeExpr(Expr $expr): ?Expr
    {
        if ($expr instanceof MethodCall) {
            return new NullsafeMethodCall($expr->var, $expr->name);
        }

        if (property_exists($expr, 'var') && property_exists($expr, self::NAME)) {
            return new NullsafePropertyFetch($expr->var, $expr->name);
        }

        return null;
    }

    private function processNullSafeExprResult(?Expr $expr, Identifier $nextExprIdentifier): ?Expr
    {
        if ($expr === null) {
            return null;
        }

        if ($expr instanceof NullsafeMethodCall) {
            return new NullsafeMethodCall($expr, $nextExprIdentifier);
        }

        return new NullsafePropertyFetch($expr, $nextExprIdentifier);
    }
}
