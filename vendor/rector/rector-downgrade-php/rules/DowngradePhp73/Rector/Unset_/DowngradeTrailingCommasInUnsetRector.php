<?php

declare (strict_types=1);
namespace Rector\DowngradePhp73\Rector\Unset_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Unset_;
use Rector\DowngradePhp73\Tokenizer\FollowedByCommaAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp73\Rector\Unset_\DowngradeTrailingCommasInUnsetRector\DowngradeTrailingCommasInUnsetRectorTest
 */
final class DowngradeTrailingCommasInUnsetRector extends AbstractRector
{
    /**
     * @readonly
     */
    private FollowedByCommaAnalyzer $followedByCommaAnalyzer;
    public function __construct(FollowedByCommaAnalyzer $followedByCommaAnalyzer)
    {
        $this->followedByCommaAnalyzer = $followedByCommaAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove trailing commas in unset', [new CodeSample(<<<'CODE_SAMPLE'
unset(
	$x,
	$y,
);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
unset(
	$x,
	$y
);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Unset_::class];
    }
    /**
     * @param Unset_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->vars !== []) {
            $lastArgumentPosition = \array_key_last($node->vars);
            $last = $node->vars[$lastArgumentPosition];
            if (!$this->followedByCommaAnalyzer->isFollowed($this->file, $last)) {
                return null;
            }
            // remove comma
            $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            return $node;
        }
        return null;
    }
}
