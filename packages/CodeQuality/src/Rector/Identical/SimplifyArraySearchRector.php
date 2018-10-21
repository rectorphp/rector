<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class SimplifyArraySearchRector extends AbstractRector
{
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
        $match = $this->matchArraySearchFuncCallAndBoolConstFetch($node);
        if ($match === null) {
            return null;
        }

        [$arraySearchFuncCallNode, $boolConstFetchNode] = $match;

        $inArrayFuncCall = new FuncCall(new Name('in_array'), [
            $arraySearchFuncCallNode->args[0],
            $arraySearchFuncCallNode->args[1],
        ]);

        if ($this->resolveIsNot($node, $boolConstFetchNode)) {
            return new BooleanNot($inArrayFuncCall);
        }

        return $inArrayFuncCall;
    }

    /**
     * @return ConstFetch[]|FuncCall[]
     */
    private function matchArraySearchFuncCallAndBoolConstFetch(BinaryOp $binaryOpNode): ?array
    {
        if ($this->isName($binaryOpNode->left, 'array_search') &&
            $this->isBool($binaryOpNode->right)
        ) {
            $arraySearchFuncCallNode = $binaryOpNode->left;
            $boolConstFetchNode = $binaryOpNode->right;
        } elseif ($this->isBool($binaryOpNode->left) &&
            $this->isName($binaryOpNode->right, 'array_search')
        ) {
            $arraySearchFuncCallNode = $binaryOpNode->right;
            $boolConstFetchNode = $binaryOpNode->left;
        } else {
            return null;
        }

        return [$arraySearchFuncCallNode, $boolConstFetchNode];
    }

    private function resolveIsNot(BinaryOp $node, ConstFetch $boolConstFetchNode): bool
    {
        if ($node instanceof Identical) {
            return $this->isFalse($boolConstFetchNode);
        }

        return $this->isTrue($boolConstFetchNode);
    }
}
