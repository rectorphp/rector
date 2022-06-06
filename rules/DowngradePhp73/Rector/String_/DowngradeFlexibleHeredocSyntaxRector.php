<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp73\Rector\String_;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Scalar\Encapsed;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp73\Rector\String_\DowngradeFlexibleHeredocSyntaxRector\DowngradeFlexibleHeredocSyntaxRectorTest
 */
final class DowngradeFlexibleHeredocSyntaxRector extends AbstractRector
{
    /**
     * @var int[]
     */
    private const HERENOW_DOC_KINDS = [String_::KIND_HEREDOC, String_::KIND_NOWDOC];
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
        return [String_::class, Encapsed::class];
    }
    /**
     * @param Encapsed|String_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $stringKind = $node->getAttribute(AttributeKey::KIND);
        if (!\in_array($stringKind, self::HERENOW_DOC_KINDS, \true)) {
            return null;
        }
        // skip correctly indented
        $docIndentation = (string) $node->getAttribute(AttributeKey::DOC_INDENTATION);
        if ($docIndentation === '') {
            return null;
        }
        $node->setAttribute(AttributeKey::DOC_INDENTATION, '');
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
}
