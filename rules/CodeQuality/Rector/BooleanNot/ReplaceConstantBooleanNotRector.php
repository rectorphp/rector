<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\BooleanNot;

use PhpParser\Node;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * Replace negated boolean literals with their simplified equivalents
 *
 * @see \Rector\Tests\CodeQuality\Rector\BooleanNot\ReplaceConstantBooleanNotRector\ReplaceConstantBooleanNotRectorTest
 */
final class ReplaceConstantBooleanNotRector extends AbstractRector
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
        return new RuleDefinition('Replace negated boolean literals (!false, !true) with their simplified equivalents (true, false)', [new CodeSample(<<<'CODE_SAMPLE'
if (!false) {
    return 'always true';
}

if (!true) {
    return 'never reached';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
if (true) {
    return 'always true';
}

if (false) {
    return 'never reached';
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [BooleanNot::class];
    }
    /**
     * @param BooleanNot $node
     */
    public function refactor(Node $node): ?Node
    {
        // unwrap the whole negation chain at once, so nested cases like !!false resolve in a single pass
        $negationCount = 0;
        $innerExpr = $node;
        while ($innerExpr instanceof BooleanNot) {
            ++$negationCount;
            $innerExpr = $innerExpr->expr;
        }
        if ($this->valueResolver->isTrue($innerExpr)) {
            $value = \true;
        } elseif ($this->valueResolver->isFalse($innerExpr)) {
            $value = \false;
        } else {
            return null;
        }
        // every negation flips the literal value
        if ($negationCount % 2 === 1) {
            $value = !$value;
        }
        return new ConstFetch(new Name($value ? 'true' : 'false'));
    }
}
