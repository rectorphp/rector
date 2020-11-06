<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
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
            $prevNode = $node->getAttribute(AttributeKey::PREVIOUS_NODE);
            while ($prevNode) {
                $assign = $this->betterNodeFinder->findFirst($prevNode, function (Node $n) use ($node): bool {
                    return $n instanceof Assign && $n->var->name === $node->cond->left->name;
                });

                if ($assign) {
                    $this->removeNode($prevNode);
                    return new NullsafeMethodCall($assign->expr->var, $assign->expr->name);
                }

                $prevNode = $prevNode->getAttribute(AttributeKey::PREVIOUS_NODE);
            }
        }

        return null;
    }
}
