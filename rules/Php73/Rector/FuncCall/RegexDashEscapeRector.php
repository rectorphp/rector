<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Php73\Rector\FuncCall;

use RectorPrefix20220606\Nette\Utils\Strings;
use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\Rector\Core\Php\Regex\RegexPatternArgumentManipulator;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Core\Util\StringUtils;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersionFeature;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://3v4l.org/dRG8U
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
     * @see https://regex101.com/r/TBVme9/8
     */
    private const RIGHT_HAND_UNESCAPED_DASH_REGEX = '#(?<!\\[)-(\\\\(w|s|d)[.*]?)\\]#i';
    /**
     * @readonly
     * @var \Rector\Core\Php\Regex\RegexPatternArgumentManipulator
     */
    private $regexPatternArgumentManipulator;
    public function __construct(RegexPatternArgumentManipulator $regexPatternArgumentManipulator)
    {
        $this->regexPatternArgumentManipulator = $regexPatternArgumentManipulator;
    }
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
        return [FuncCall::class, StaticCall::class];
    }
    /**
     * @param FuncCall|StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        $regexArguments = $this->regexPatternArgumentManipulator->matchCallArgumentWithRegexPattern($node);
        if ($regexArguments === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($regexArguments as $regexArgument) {
            if (StringUtils::isMatch($regexArgument->value, self::THREE_BACKSLASH_FOR_ESCAPE_NEXT_REGEX)) {
                continue;
            }
            $this->escapeStringNode($regexArgument);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function escapeStringNode(String_ $string) : void
    {
        $stringValue = $string->value;
        if (StringUtils::isMatch($stringValue, self::LEFT_HAND_UNESCAPED_DASH_REGEX)) {
            $string->value = Strings::replace($stringValue, self::LEFT_HAND_UNESCAPED_DASH_REGEX, '$1\\-');
            // helped needed to skip re-escaping regular expression
            $string->setAttribute(AttributeKey::IS_REGULAR_PATTERN, \true);
            return;
        }
        if (StringUtils::isMatch($stringValue, self::RIGHT_HAND_UNESCAPED_DASH_REGEX)) {
            $string->value = Strings::replace($stringValue, self::RIGHT_HAND_UNESCAPED_DASH_REGEX, '\\-$1]');
            // helped needed to skip re-escaping regular expression
            $string->setAttribute(AttributeKey::IS_REGULAR_PATTERN, \true);
        }
    }
}
