<?php

declare (strict_types=1);
namespace Rector\Php82\Rector\Encapsed;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\InterpolatedString;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php82\Rector\Encapsed\VariableInStringInterpolationFixerRector\VariableInStringInterpolationFixerRectorTest
 */
final class VariableInStringInterpolationFixerRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace deprecated `${var}` to `{$var}`', [new CodeSample(<<<'CODE_SAMPLE'
$c = "football";
echo "I like playing ${c}";
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$c = "football";
echo "I like playing {$c}";
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [InterpolatedString::class];
    }
    /**
     * @param InterpolatedString $node
     */
    public function refactor(Node $node) : ?Node
    {
        $oldTokens = $this->file->getOldTokens();
        $hasChanged = \false;
        foreach ($node->parts as $part) {
            if (!$part instanceof Variable) {
                continue;
            }
            $startTokenPos = $part->getStartTokenPos();
            if (!isset($oldTokens[$startTokenPos])) {
                continue;
            }
            if ((string) $oldTokens[$startTokenPos] !== '${') {
                continue;
            }
            $part->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATE_VARIABLE_IN_STRING_INTERPOLATION;
    }
}
