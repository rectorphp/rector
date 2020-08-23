<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\BooleanAnd;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\Empty_;
use Rector\Core\PhpParser\Node\Manipulator\BinaryOpManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/EZ2P4
 * @see https://3v4l.org/egtb5
 * @see \Rector\CodeQuality\Tests\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector\SimplifyEmptyArrayCheckRectorTest
 */
final class SimplifyEmptyArrayCheckRector extends AbstractRector
{
    /**
     * @var BinaryOpManipulator
     */
    private $binaryOpManipulator;

    public function __construct(BinaryOpManipulator $binaryOpManipulator)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
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
        $twoNodeMatch = $this->binaryOpManipulator->matchFirstAndSecondConditionNode(
            $node,
            // is_array(...)
            function (Node $node): bool {
                return $this->isFuncCallName($node, 'is_array');
            },
            Empty_::class
        );

        if ($twoNodeMatch === null) {
            return null;
        }

        /** @var Empty_ $emptyOrNotIdenticalNode */
        $emptyOrNotIdenticalNode = $twoNodeMatch->getSecondExpr();

        return new Identical($emptyOrNotIdenticalNode->expr, new Array_());
    }
}
