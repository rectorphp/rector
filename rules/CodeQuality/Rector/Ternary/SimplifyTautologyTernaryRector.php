<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\Ternary;
use Rector\Core\NodeManipulator\BinaryOpManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Php71\ValueObject\TwoNodeMatch;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Ternary\SimplifyTautologyTernaryRector\SimplifyTautologyTernaryRectorTest
 */
final class SimplifyTautologyTernaryRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\BinaryOpManipulator
     */
    private $binaryOpManipulator;
    public function __construct(\Rector\Core\NodeManipulator\BinaryOpManipulator $binaryOpManipulator)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Simplify tautology ternary to value', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('$value = ($fullyQualifiedTypeHint !== $typeHint) ? $fullyQualifiedTypeHint : $typeHint;', '$value = $fullyQualifiedTypeHint;')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Ternary::class];
    }
    /**
     * @param Ternary $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node->cond instanceof \PhpParser\Node\Expr\BinaryOp\NotIdentical && !$node->cond instanceof \PhpParser\Node\Expr\BinaryOp\Identical) {
            return null;
        }
        $twoNodeMatch = $this->binaryOpManipulator->matchFirstAndSecondConditionNode($node->cond, function (\PhpParser\Node $leftNode) use($node) : bool {
            return $this->nodeComparator->areNodesEqual($leftNode, $node->if);
        }, function (\PhpParser\Node $leftNode) use($node) : bool {
            return $this->nodeComparator->areNodesEqual($leftNode, $node->else);
        });
        if (!$twoNodeMatch instanceof \Rector\Php71\ValueObject\TwoNodeMatch) {
            return null;
        }
        return $node->if;
    }
}
