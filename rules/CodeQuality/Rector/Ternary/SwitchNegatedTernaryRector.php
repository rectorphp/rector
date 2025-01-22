<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Ternary;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector\SwitchNegatedTernaryRectorTest
 */
final class SwitchNegatedTernaryRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Switch negated ternary condition rector', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(bool $upper, string $name)
    {
        return ! $upper
            ? $name
            : strtoupper($name);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(bool $upper, string $name)
    {
        return $upper
            ? strtoupper($name)
            : $name;
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
        return [Ternary::class];
    }
    /**
     * @param Ternary $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->cond instanceof BooleanNot) {
            return null;
        }
        if (!$node->if instanceof Expr) {
            return null;
        }
        $node->cond = $node->cond->expr;
        $else = clone $node->else;
        $if = clone $node->if;
        $node->else = $if;
        $node->if = $else;
        if ($node->if instanceof Ternary) {
            $ternary = $node->if;
            $ternary->setAttribute(AttributeKey::KIND, AttributeKey::WRAPPED_IN_PARENTHESES);
            $ternary->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        }
        if ($node->else instanceof Ternary) {
            $ternary = $node->else;
            $ternary->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        }
        return $node;
    }
}
