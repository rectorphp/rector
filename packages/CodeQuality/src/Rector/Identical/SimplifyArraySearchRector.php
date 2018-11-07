<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use Rector\PhpParser\Node\Maintainer\BinaryOpMaintainer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SimplifyArraySearchRector extends AbstractRector
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
            'Simplify array_search to in_array',
            [
                new CodeSample(
                    'array_search("searching", $array) !== false;',
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
     * @param NotIdentical $node
     */
    public function refactor(Node $node): ?Node
    {
        $matchedNodes = $this->binaryOpMaintainer->matchFirstAndSecondConditionNode(
            $node,
            function (Node $node) {
                return $node instanceof FuncCall && $this->isName($node, 'array_search');
            },
            function (Node $node) {
                return $this->isBool($node);
            }
        );

        if ($matchedNodes === null) {
            return null;
        }

        /** @var FuncCall $arraySearchFuncCallNode */
        /** @var ConstFetch $boolConstFetchNode */
        [$arraySearchFuncCallNode, $boolConstFetchNode] = $matchedNodes;

        $inArrayFuncCall = $this->createFunction('in_array', [
            $arraySearchFuncCallNode->args[0],
            $arraySearchFuncCallNode->args[1],
        ]);

        if ($this->resolveIsNot($node, $boolConstFetchNode)) {
            return new BooleanNot($inArrayFuncCall);
        }

        return $inArrayFuncCall;
    }

    private function resolveIsNot(BinaryOp $node, ConstFetch $boolConstFetchNode): bool
    {
        if ($node instanceof Identical) {
            return $this->isFalse($boolConstFetchNode);
        }

        return $this->isTrue($boolConstFetchNode);
    }
}
