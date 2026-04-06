<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Scalar\Int_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\IntegerType;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Ternary\RemoveUselessTernaryRector\RemoveUselessTernaryRectorTest
 */
final class RemoveUselessTernaryRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove useless ternary if fallback is falsey of left code', [new CodeSample(<<<'CODE_SAMPLE'
function go(bool $value)
{
    return $value ?: false;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function go(bool $value)
{
    return $value;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Ternary::class];
    }
    /**
     * @param Ternary $node
     */
    public function refactor(Node $node): ?Node
    {
        /**
         * if condition is negated, skip
         * switch negated ternary condition early via SwitchNegatedTernaryRector for that
         * if needed
         */
        if ($node->cond instanceof BooleanNot) {
            return null;
        }
        $nativeType = $this->nodeTypeResolver->getNativeType($node->cond);
        if ($nativeType instanceof BooleanType && $node->if instanceof Expr && $this->valueResolver->isTrue($node->if) && $this->valueResolver->isFalse($node->else)) {
            return $node->cond;
        }
        if ($node->if instanceof Expr && !$this->nodeComparator->areNodesEqual($node->if, $node->cond)) {
            return null;
        }
        if ($nativeType instanceof BooleanType && $this->valueResolver->isFalse($node->else)) {
            return $node->cond;
        }
        if ($nativeType instanceof ArrayType && $node->else instanceof Array_ && $node->else->items === []) {
            return $node->cond;
        }
        if ($nativeType instanceof IntegerType && $node->else instanceof Int_ && $node->else->value === 0) {
            return $node->cond;
        }
        return null;
    }
}
