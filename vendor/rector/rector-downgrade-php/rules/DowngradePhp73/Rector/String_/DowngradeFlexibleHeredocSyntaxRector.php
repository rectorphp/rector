<?php

declare (strict_types=1);
namespace Rector\DowngradePhp73\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\Node\Scalar\String_;
use Rector\DowngradePhp73\Tokenizer\FollowedByNewlineOnlyMaybeWithSemicolonAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp73\Rector\String_\DowngradeFlexibleHeredocSyntaxRector\DowngradeFlexibleHeredocSyntaxRectorTest
 */
final class DowngradeFlexibleHeredocSyntaxRector extends AbstractRector
{
    /**
     * @readonly
     */
    private FollowedByNewlineOnlyMaybeWithSemicolonAnalyzer $followedByNewlineOnlyMaybeWithSemicolonAnalyzer;
    /**
     * @var int[]
     */
    private const HERENOW_DOC_KINDS = [String_::KIND_HEREDOC, String_::KIND_NOWDOC];
    public function __construct(FollowedByNewlineOnlyMaybeWithSemicolonAnalyzer $followedByNewlineOnlyMaybeWithSemicolonAnalyzer)
    {
        $this->followedByNewlineOnlyMaybeWithSemicolonAnalyzer = $followedByNewlineOnlyMaybeWithSemicolonAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove indentation from heredoc/nowdoc', [new CodeSample(<<<'CODE_SAMPLE'
$query = <<<SQL
    SELECT *
    FROM `table`
    WHERE `column` = true;
    SQL;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$query = <<<SQL
SELECT *
FROM `table`
WHERE `column` = true;
SQL;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [String_::class, InterpolatedString::class];
    }
    /**
     * @param InterpolatedString|String_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $stringKind = $node->getAttribute(AttributeKey::KIND);
        if (!\in_array($stringKind, self::HERENOW_DOC_KINDS, \true)) {
            return null;
        }
        // skip correctly indented
        $docIndentation = (string) $node->getAttribute(AttributeKey::DOC_INDENTATION);
        if ($docIndentation === '' && $this->followedByNewlineOnlyMaybeWithSemicolonAnalyzer->isFollowed($this->file, $node)) {
            return null;
        }
        $node->setAttribute(AttributeKey::DOC_INDENTATION, '__REMOVED__');
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        $tokens = $this->file->getOldTokens();
        if (isset($tokens[$node->getEndTokenPos()], $tokens[$node->getEndTokenPos() + 1])) {
            $tokens[$node->getEndTokenPos() + 1]->text = "\n" . $tokens[$node->getEndTokenPos() + 1]->text;
        }
        return $node;
    }
}
