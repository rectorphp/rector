<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\NullsafeMethodCall;
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
            return $this->processNullSafeOperator($node);
        }

        return null;
    }

    private function processNullSafeOperator(If_ $if): ?Node
    {
        $nextNode = $if->getAttribute(AttributeKey::NEXT_NODE);
        if ($nextNode === null) {
            return null;
        }

        $prevNode = $if->getAttribute(AttributeKey::PREVIOUS_NODE);
        while ($prevNode) {
            /** @var Assign|null $assign */
            $assign = $this->betterNodeFinder->findFirst($prevNode, function (Node $node) use ($if): bool {
                return $node instanceof Assign && $if->cond instanceof Identical && $this->getName(
                    $if->cond->left
                ) === $this->getName($node->var);
            });

            $processAssign = $this->processAssign($assign, $prevNode, $nextNode, $if);
            if ($processAssign instanceof Node) {
                return $processAssign;
            }

            $prevNode = $prevNode->getAttribute(AttributeKey::PREVIOUS_NODE);
        }

        return null;
    }

    private function processAssign(?Assign $assign, Node $prevNode, Node $nextNode, If_ $if): ?Node
    {
        if ($assign instanceof Assign && property_exists(
            $assign->expr,
            'name'
        ) && $nextNode->expr instanceof Expr && property_exists($nextNode->expr, 'name')) {
            $this->removeNode($prevNode);

            if ($nextNode instanceof Return_) {
                $this->removeNode($nextNode);

                return new Return_(
                    new NullsafeMethodCall(
                        new NullsafeMethodCall(
                            property_exists($assign->expr, 'var') ? $assign->expr->var : $assign->expr,
                            $assign->expr->name
                        ),
                        $nextNode->expr->name
                    )
                );
            }
        }

        return null;
    }
}
