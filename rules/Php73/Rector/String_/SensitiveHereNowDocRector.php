<?php

declare (strict_types=1);
namespace Rector\Php73\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/flexible_heredoc_nowdoc_syntaxes
 * @see \Rector\Tests\Php73\Rector\String_\SensitiveHereNowDocRector\SensitiveHereNowDocRectorTest
 */
final class SensitiveHereNowDocRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var string
     */
    private const WRAP_SUFFIX = '_WRAP';
    /**
     * @var string
     */
    private const ATTRIBUTE_DOC_LABEL = 'docLabel';
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::SENSITIVE_HERE_NOW_DOC;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes heredoc/nowdoc that contains closing word to safe wrapper name', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$value = <<<A
    A
A
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$value = <<<A_WRAP
    A
A_WRAP
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Scalar\String_::class];
    }
    /**
     * @param String_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $kind = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::KIND);
        if (!\in_array($kind, [\PhpParser\Node\Scalar\String_::KIND_HEREDOC, \PhpParser\Node\Scalar\String_::KIND_NOWDOC], \true)) {
            return null;
        }
        // the doc label is not in the string → ok
        /** @var string $docLabel */
        $docLabel = $node->getAttribute(self::ATTRIBUTE_DOC_LABEL);
        if (\strpos($node->value, $docLabel) === \false) {
            return null;
        }
        $node->setAttribute(self::ATTRIBUTE_DOC_LABEL, $this->uniquateDocLabel($node->value, $docLabel));
        // invoke redraw
        $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
    private function uniquateDocLabel(string $value, string $docLabel) : string
    {
        $docLabel .= self::WRAP_SUFFIX;
        $docLabelCounterTemplate = $docLabel . '_%d';
        $i = 0;
        while (\strpos($value, $docLabel) !== \false) {
            $docLabel = \sprintf($docLabelCounterTemplate, ++$i);
        }
        return $docLabel;
    }
}
