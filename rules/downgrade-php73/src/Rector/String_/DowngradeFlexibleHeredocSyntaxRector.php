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
     * @var string
     */
    public const DOC_INDENTATION = 'docIndentation';

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
        $kind = $node->getAttribute(AttributeKey::KIND);
        if (! in_array($kind, [String_::KIND_HEREDOC, String_::KIND_NOWDOC], true)) {
            return null;
        }

        $node->setAttribute(self::DOC_INDENTATION, '');

        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);

        return $node;
    }
}
