<?php

declare (strict_types=1);
namespace Rector\DowngradePhp73\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp73\Rector\String_\DowngradeFlexibleHeredocSyntaxRector\DowngradeFlexibleHeredocSyntaxRectorTest
 */
final class DowngradeFlexibleHeredocSyntaxRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var int[]
     */
    private const HERENOW_DOC_KINDS = [\PhpParser\Node\Scalar\String_::KIND_HEREDOC, \PhpParser\Node\Scalar\String_::KIND_NOWDOC];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove indentation from heredoc/nowdoc', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Scalar\String_::class, \PhpParser\Node\Scalar\Encapsed::class];
    }
    /**
     * @param Encapsed|String_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $stringKind = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::KIND);
        if (!\in_array($stringKind, self::HERENOW_DOC_KINDS, \true)) {
            return null;
        }
        // skip correctly indented
        $docIndentation = (string) $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::DOC_INDENTATION);
        if ($docIndentation === '') {
            return null;
        }
        $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::DOC_INDENTATION, '');
        $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
}
