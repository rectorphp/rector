<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\PhpParser\Node\Manipulator\BinaryOpManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\Identical\SimplifyArraySearchRector\SimplifyArraySearchRectorTest
 */
final class SimplifyArraySearchRector extends AbstractRector
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
            'Simplify array_search to in_array',
            [
                new CodeSample('array_search("searching", $array) !== false;', 'in_array("searching", $array);'),
                new CodeSample(
                    'array_search("searching", $array, true) !== false;',
                    'in_array("searching", $array, true);'
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Identical::class, NotIdentical::class];
    }

    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node): ?Node
    {
        $twoNodeMatch = $this->binaryOpManipulator->matchFirstAndSecondConditionNode(
            $node,
            function (Node $node): bool {
                return $this->isFuncCallName($node, 'array_search');
            },
            function (Node $node): bool {
                return $this->isFalse($node);
            }
        );

        if ($twoNodeMatch === null) {
            return null;
        }

        /** @var FuncCall $arraySearchFuncCall */
        $arraySearchFuncCall = $twoNodeMatch->getFirstExpr();

        $inArrayFuncCall = $this->createFuncCall('in_array', $arraySearchFuncCall->args);

        if ($node instanceof Identical) {
            return new BooleanNot($inArrayFuncCall);
        }

        return $inArrayFuncCall;
    }
}
