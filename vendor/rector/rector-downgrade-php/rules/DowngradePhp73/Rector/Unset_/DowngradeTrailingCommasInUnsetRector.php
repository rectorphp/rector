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
     * @var \Rector\DowngradePhp73\Tokenizer\FollowedByCommaAnalyzer
     */
    private $followedByCommaAnalyzer;
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
            \end($node->vars);
            $lastArgumentPosition = \key($node->vars);
            \reset($node->vars);
            $last = $node->vars[$lastArgumentPosition];
            if (!$this->followedByCommaAnalyzer->isFollowed($this->file, $last)) {
                return null;
            }
            // remove comma
            $last->setAttribute(AttributeKey::FUNC_ARGS_TRAILING_COMMA, \false);
            $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            return $node;
        }
        return null;
    }
}
