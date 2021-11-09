<?php

declare (strict_types=1);
namespace Rector\DowngradePhp54\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/shortsyntaxforarrays
 *
 * @see \Rector\Tests\DowngradePhp54\Rector\Array_\ShortArrayToLongArrayRector\ShortArrayToLongArrayRectorTest
 */
final class ShortArrayToLongArrayRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace short arrays by long arrays', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$a = [1, 2, 3];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$a = array(1, 2, 3);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Array_::class];
    }
    /**
     * @param Array_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node\Expr\Array_
    {
        $kind = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::KIND);
        if ($kind === \PhpParser\Node\Expr\Array_::KIND_LONG) {
            return null;
        }
        $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::KIND, \PhpParser\Node\Expr\Array_::KIND_LONG);
        // force php-parser to re-print: https://github.com/rectorphp/rector/issues/6618#issuecomment-893226087
        $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
}
