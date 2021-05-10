<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp;
use Rector\Core\PhpParser\Node\AssignAndBinaryMap;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Assign\CombinedAssignRector\CombinedAssignRectorTest
 */
final class CombinedAssignRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Core\PhpParser\Node\AssignAndBinaryMap
     */
    private $assignAndBinaryMap;
    public function __construct(\Rector\Core\PhpParser\Node\AssignAndBinaryMap $assignAndBinaryMap)
    {
        $this->assignAndBinaryMap = $assignAndBinaryMap;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Simplify $value = $value + 5; assignments to shorter ones', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$value = $value + 5;', '$value += 5;')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node->expr instanceof \PhpParser\Node\Expr\BinaryOp) {
            return null;
        }
        /** @var BinaryOp $binaryNode */
        $binaryNode = $node->expr;
        if (!$this->nodeComparator->areNodesEqual($node->var, $binaryNode->left)) {
            return null;
        }
        $assignClass = $this->assignAndBinaryMap->getAlternative($binaryNode);
        if ($assignClass === null) {
            return null;
        }
        return new $assignClass($node->var, $binaryNode->right);
    }
}
