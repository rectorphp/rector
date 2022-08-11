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
final class DowngradeBinaryNotationRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade binary notation for integers', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [LNumber::class];
    }
    /**
     * @param LNumber $node
     */
    public function refactor(Node $node) : ?LNumber
    {
        $kind = $node->getAttribute(AttributeKey::KIND);
        if ($kind !== LNumber::KIND_BIN) {
            return null;
        }
        $node->setAttribute(AttributeKey::KIND, LNumber::KIND_DEC);
        // force php-parser to re-print: https://github.com/rectorphp/rector/issues/6618#issuecomment-893226087
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
}
