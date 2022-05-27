<?php

declare (strict_types=1);
namespace Rector\DowngradePhp54\Rector\LNumber;

use PhpParser\Node;
use PhpParser\Node\Scalar\LNumber;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/binnotation4ints
 *
 * @see \Rector\Tests\DowngradePhp54\Rector\LNumber\DowngradeBinaryNotationRector\DowngradeBinaryNotationRectorTest
 */
final class DowngradeBinaryNotationRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Downgrade binary notation for integers', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$a = 0b11111100101;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$a = 2021;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Scalar\LNumber::class];
    }
    /**
     * @param LNumber $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node\Scalar\LNumber
    {
        $kind = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::KIND);
        if ($kind !== \PhpParser\Node\Scalar\LNumber::KIND_BIN) {
            return null;
        }
        $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::KIND, \PhpParser\Node\Scalar\LNumber::KIND_DEC);
        // force php-parser to re-print: https://github.com/rectorphp/rector/issues/6618#issuecomment-893226087
        $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
}
