<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\Rector\LNumber;

use PhpParser\Node;
use PhpParser\Node\Scalar\LNumber;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://php.watch/versions/8.1/explicit-octal-notation
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\LNumber\DowngradeOctalNumberRector\DowngradeOctalNumberRectorTest
 */
final class DowngradeOctalNumberRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrades octal numbers to decimal ones', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return 0o777;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return 0777;
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
        return [LNumber::class];
    }
    /**
     * @param LNumber $node
     */
    public function refactor(Node $node) : ?Node
    {
        $numberKind = $node->getAttribute(AttributeKey::KIND);
        if ($numberKind !== LNumber::KIND_OCT) {
            return null;
        }
        /** @var string $rawValue */
        $rawValue = $node->getAttribute(AttributeKey::RAW_VALUE);
        if (\strncmp($rawValue, '0o', \strlen('0o')) !== 0 && \strncmp($rawValue, '0O', \strlen('0O')) !== 0) {
            return null;
        }
        $clearValue = '0' . \substr($rawValue, 2);
        $node->setAttribute(AttributeKey::RAW_VALUE, $clearValue);
        // invoke reprint
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
}
