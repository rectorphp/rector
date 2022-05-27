<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\Double;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\Double;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/deprecations_php_7_4
 * @see \Rector\Tests\Php74\Rector\Double\RealToFloatTypeCastRector\RealToFloatTypeCastRectorTest
 */
final class RealToFloatTypeCastRector extends AbstractRector implements MinPhpVersionInterface
{
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATE_REAL;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change deprecated (real) to (float)', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $number = (real) 5;
        $number = (float) 5;
        $number = (double) 5;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $number = (float) 5;
        $number = (float) 5;
        $number = (double) 5;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Double::class];
    }
    /**
     * @param Double $node
     */
    public function refactor(Node $node) : ?Node
    {
        $kind = $node->getAttribute(AttributeKey::KIND);
        if ($kind !== Double::KIND_REAL) {
            return null;
        }
        $node->setAttribute(AttributeKey::KIND, Double::KIND_FLOAT);
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
}
