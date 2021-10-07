<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\Ternary;
use PHPStan\Type\BooleanType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector\TernaryToBooleanOrFalseToBooleanAndRectorTest
 */
final class TernaryToBooleanOrFalseToBooleanAndRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change ternary of bool : false to && bool', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function go()
    {
        return $value ? $this->getBool() : false;
    }

    private function getBool(): bool
    {
        return (bool) 5;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function go()
    {
        return $value && $this->getBool();
    }

    private function getBool(): bool
    {
        return (bool) 5;
    }
}
CODE_SAMPLE
)]);
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
        if ($node->if === null) {
            return null;
        }
        if (!$this->valueResolver->isFalse($node->else)) {
            return null;
        }
        if ($this->valueResolver->isTrue($node->if)) {
            return null;
        }
        $ifType = $this->getType($node->if);
        if (!$ifType instanceof \PHPStan\Type\BooleanType) {
            return null;
        }
        return new \PhpParser\Node\Expr\BinaryOp\BooleanAnd($node->cond, $node->if);
    }
}
