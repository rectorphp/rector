<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\BooleanNot;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BooleanNot;
use Rector\NodeManipulator\BinaryOpManipulator;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector\SimplifyDeMorganBinaryRectorTest
 */
final class SimplifyDeMorganBinaryRector extends AbstractRector
{
    /**
     * @readonly
     */
    private BinaryOpManipulator $binaryOpManipulator;
    public function __construct(BinaryOpManipulator $binaryOpManipulator)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Simplify negated conditions with de Morgan theorem', [new CodeSample(<<<'CODE_SAMPLE'
$a = 5;
$b = 10;
$result = !($a > 20 || $b <= 50);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$a = 5;
$b = 10;
$result = $a <= 20 && $b > 50;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [BooleanNot::class];
    }
    /**
     * @param BooleanNot $node
     */
    public function refactor(Node $node) : ?BinaryOp
    {
        if (!$node->expr instanceof BooleanOr) {
            return null;
        }
        return $this->binaryOpManipulator->inverseBooleanOr($node->expr);
    }
}
