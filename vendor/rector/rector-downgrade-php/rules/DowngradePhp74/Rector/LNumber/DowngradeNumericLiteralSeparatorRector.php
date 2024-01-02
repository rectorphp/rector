<?php

declare (strict_types=1);
namespace Rector\DowngradePhp74\Rector\LNumber;

use PhpParser\Node;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/numeric_literal_separator
 *
 * @see \Rector\Tests\DowngradePhp74\Rector\LNumber\DowngradeNumericLiteralSeparatorRector\DowngradeNumericLiteralSeparatorRectorTest
 */
final class DowngradeNumericLiteralSeparatorRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove "_" as thousands separator in numbers', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $int = 1_000;
        $float = 1_000_500.001;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $int = 1000;
        $float = 1000500.001;
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
        return [LNumber::class, DNumber::class];
    }
    /**
     * @param LNumber|DNumber $node
     */
    public function refactor(Node $node) : ?Node
    {
        $rawValue = $node->getAttribute(AttributeKey::RAW_VALUE);
        if ($this->shouldSkip($node, $rawValue)) {
            return null;
        }
        if (\strpos((string) $rawValue, '+') !== \false) {
            return null;
        }
        $rawValueWithoutUnderscores = \str_replace('_', '', (string) $rawValue);
        $node->setAttribute(AttributeKey::RAW_VALUE, $rawValueWithoutUnderscores);
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
    /**
     * @param \PhpParser\Node\Scalar\LNumber|\PhpParser\Node\Scalar\DNumber $node
     * @param mixed $rawValue
     */
    private function shouldSkip($node, $rawValue) : bool
    {
        if (!\is_string($rawValue)) {
            return \true;
        }
        // "_" notation can be applied to decimal numbers only
        if ($node instanceof LNumber) {
            $numberKind = $node->getAttribute(AttributeKey::KIND);
            if ($numberKind !== LNumber::KIND_DEC) {
                return \true;
            }
        }
        return \strpos($rawValue, '_') === \false;
    }
}
