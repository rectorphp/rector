<?php

declare (strict_types=1);
namespace Rector\Php54\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php54\Rector\Array_\LongArrayToShortArrayRector\LongArrayToShortArrayRectorTest
 */
final class LongArrayToShortArrayRector extends AbstractRector implements MinPhpVersionInterface
{
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::SHORT_ARRAY;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Long array to short array', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return array();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return [];
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
        return [Array_::class];
    }
    /**
     * @param Array_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->getAttribute(AttributeKey::KIND) === Array_::KIND_SHORT) {
            return null;
        }
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        $node->setAttribute(AttributeKey::KIND, Array_::KIND_SHORT);
        return $node;
    }
}
