<?php

declare (strict_types=1);
namespace Rector\Php53\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr\Ternary;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php53\Rector\Ternary\TernaryToElvisRector\TernaryToElvisRectorTest
 */
final class TernaryToElvisRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use ?: instead of ?, where useful', [new CodeSample(<<<'CODE_SAMPLE'
function elvis()
{
    $value = $a ? $a : false;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function elvis()
{
    $value = $a ?: false;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Ternary::class];
    }
    /**
     * @param Ternary $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->nodeComparator->areNodesEqual($node->cond, $node->if)) {
            return null;
        }
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        $node->if = null;
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ELVIS_OPERATOR;
    }
}
