<?php

declare (strict_types=1);
namespace Rector\Php73\Rector\FuncCall;

use RectorPrefix202407\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\Util\StringUtils;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php73\Rector\FuncCall\RegexDashEscapeRector\RegexDashEscapeRectorTest
 */
final class RegexDashEscapeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var string
     * @see https://regex101.com/r/iQbGgZ/1
     *
     * Use {2} as detected only 2 after $this->regexPatternArgumentManipulator->matchCallArgumentWithRegexPattern() call
     */
    private const THREE_BACKSLASH_FOR_ESCAPE_NEXT_REGEX = '#(?<=[^\\\\])\\\\{2}(?=[^\\\\])#';
    /**
     * @var string
     * @see https://regex101.com/r/YgVJFp/1
     */
    private const LEFT_HAND_UNESCAPED_DASH_REGEX = '#(\\[.*?\\\\(w|s|d))-(?!\\])#i';
    /**
     * @var string
     * @see https://regex101.com/r/TBVme9/9
     */
    private const RIGHT_HAND_UNESCAPED_DASH_REGEX = '#(?<!\\[)(?<!\\\\)-(\\\\(w|s|d)[.*]?)\\]#i';
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ESCAPE_DASH_IN_REGEX;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Escape - in some cases', [new CodeSample(<<<'CODE_SAMPLE'
preg_match("#[\w-()]#", 'some text');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
preg_match("#[\w\-()]#", 'some text');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [String_::class];
    }
    /**
     * @param String_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $stringKind = $node->getAttribute(AttributeKey::KIND);
        if (\in_array($stringKind, [String_::KIND_HEREDOC, String_::KIND_NOWDOC], \true)) {
            return null;
        }
        if (StringUtils::isMatch($node->value, self::THREE_BACKSLASH_FOR_ESCAPE_NEXT_REGEX)) {
            return null;
        }
        $stringValue = $node->value;
        if (StringUtils::isMatch($stringValue, self::LEFT_HAND_UNESCAPED_DASH_REGEX)) {
            $node->value = Strings::replace($stringValue, self::LEFT_HAND_UNESCAPED_DASH_REGEX, '$1\\-');
            // helped needed to skip re-escaping regular expression
            $node->setAttribute(AttributeKey::IS_REGULAR_PATTERN, \true);
            return $node;
        }
        if (StringUtils::isMatch($stringValue, self::RIGHT_HAND_UNESCAPED_DASH_REGEX)) {
            $node->value = Strings::replace($stringValue, self::RIGHT_HAND_UNESCAPED_DASH_REGEX, '\\-$1]');
            // helped needed to skip re-escaping regular expression
            $node->setAttribute(AttributeKey::IS_REGULAR_PATTERN, \true);
            return $node;
        }
        return null;
    }
}
