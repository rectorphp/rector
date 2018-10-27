<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\BinaryOp;
use Rector\NodeAnalyzer\AssignAndBinaryMap;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class CombinedAssignRector extends AbstractRector
{
    /**
     * @var AssignAndBinaryMap
     */
    private $assignAndBinaryMap;

    public function __construct(AssignAndBinaryMap $assignAndBinaryMap)
    {
        $this->assignAndBinaryMap = $assignAndBinaryMap;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Simplify $value = $value + 5; assignments to shorter ones',
            [new CodeSample('$value = $value + 5;', '$value += 5;')]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof BinaryOp) {
            return null;
        }

        /** @var BinaryOp $binaryNode */
        $binaryNode = $node->expr;

        if (! $this->areNodesEqual($node->var, $binaryNode->left)) {
            return null;
        }

        $assignClass = $this->assignAndBinaryMap->getAlternative($binaryNode);
        if ($assignClass === null) {
            return null;
        }

        /** @var AssignOp $newAssignNodeClass */
        return new $assignClass($node->var, $binaryNode->right);
    }
}
