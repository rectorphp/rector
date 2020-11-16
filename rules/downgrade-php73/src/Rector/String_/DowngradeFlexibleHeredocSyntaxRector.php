<?php

declare(strict_types=1);

namespace Rector\DowngradePhp73\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DowngradePhp73\Tests\Rector\String_\DowngradeFlexibleHeredocSyntaxRector\DowngradeFlexibleHeredocSyntaxTest
 */
final class DowngradeFlexibleHeredocSyntaxRector extends AbstractRector
{
    /**
     * @var int[]
     */
    private const HERENOW_DOC_KINDS = [String_::KIND_HEREDOC, String_::KIND_NOWDOC];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes heredoc/nowdoc that contains closing word to safe wrapper name',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$query = <<<SQL
    SELECT *
    FROM `table`
    WHERE `column` = true;
    SQL;
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$query = <<<SQL
SELECT *
FROM `table`
WHERE `column` = true;
SQL;
CODE_SAMPLE
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [String_::class, Encapsed::class];
    }

    /**
     * @param Encapsed|String_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $stringKind = $node->getAttribute(AttributeKey::KIND);

        if (! in_array($stringKind, self::HERENOW_DOC_KINDS, true)) {
            return null;
        }

        $node->setAttribute(AttributeKey::DOC_INDENTATION, '');
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);

        return $node;
    }
}
