<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\FuncCall;
use Rector\PhpParser\Node\Maintainer\BinaryOpMaintainer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SimplifyEmptyArrayCheckRector extends AbstractRector
{
    /**
     * @var BinaryOpMaintainer
     */
    private $binaryOpMaintainer;

    public function __construct(BinaryOpMaintainer $binaryOpMaintainer)
    {
        $this->binaryOpMaintainer = $binaryOpMaintainer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Simplify `is_array` and `empty` functions combination into a simple identical check for an empty array',
            [new CodeSample('is_array($values) && empty($values)', '$values === []')]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [BooleanAnd::class];
    }

    /**
     * @param BooleanAnd $node
     */
    public function refactor(Node $node): ?Node
    {
        $matchedNodes = $this->binaryOpMaintainer->matchFirstAndSecondConditionNode(
            $node,
            // is_array(...)
            function (Node $node) {
                return $node instanceof FuncCall && $this->isName($node, 'is_array');
            },
            // empty(...) or !empty(...)
            function (Node $node) {
                if ($node instanceof Empty_) {
                    return true;
                }

                return $node instanceof BooleanNot && $node->expr instanceof Empty_;
            }
        );

        if ($matchedNodes === null) {
            return null;
        }

        /** @var Empty_|NotIdentical $emptyOrNotIdenticalNode */
        [, $emptyOrNotIdenticalNode] = $matchedNodes;

        if ($emptyOrNotIdenticalNode instanceof Empty_) {
            return new Identical($emptyOrNotIdenticalNode->expr, new Array_());
        }

        /** @var Empty_ $emptyNode */
        $emptyNode = $emptyOrNotIdenticalNode->expr;
        return new NotIdentical($emptyNode->expr, new Array_());
    }
}
