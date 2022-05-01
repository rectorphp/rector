<?php

declare (strict_types=1);
namespace Rector\Php73\Rector\FuncCall;

use RectorPrefix20220501\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Php\Regex\RegexPatternArgumentManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\StringUtils;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://3v4l.org/dRG8U
 * @see \Rector\Tests\Php73\Rector\FuncCall\RegexDashEscapeRector\RegexDashEscapeRectorTest
 */
final class RegexDashEscapeRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
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
    public function __construct(\Rector\Core\Php\Regex\RegexPatternArgumentManipulator $regexPatternArgumentManipulator)
    {
        $this->regexPatternArgumentManipulator = $regexPatternArgumentManipulator;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::ESCAPE_DASH_IN_REGEX;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Escape - in some cases', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\FuncCall::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param FuncCall|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $regexArguments = $this->regexPatternArgumentManipulator->matchCallArgumentWithRegexPattern($node);
        if ($regexArguments === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($regexArguments as $regexArgument) {
            if (\Rector\Core\Util\StringUtils::isMatch($regexArgument->value, self::THREE_BACKSLASH_FOR_ESCAPE_NEXT_REGEX)) {
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
    private function escapeStringNode(\PhpParser\Node\Scalar\String_ $string) : void
    {
        $stringValue = $string->value;
        if (\Rector\Core\Util\StringUtils::isMatch($stringValue, self::LEFT_HAND_UNESCAPED_DASH_REGEX)) {
            $string->value = \RectorPrefix20220501\Nette\Utils\Strings::replace($stringValue, self::LEFT_HAND_UNESCAPED_DASH_REGEX, '$1\\-');
            // helped needed to skip re-escaping regular expression
            $string->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::IS_REGULAR_PATTERN, \true);
            return;
        }
        if (\Rector\Core\Util\StringUtils::isMatch($stringValue, self::RIGHT_HAND_UNESCAPED_DASH_REGEX)) {
            $string->value = \RectorPrefix20220501\Nette\Utils\Strings::replace($stringValue, self::RIGHT_HAND_UNESCAPED_DASH_REGEX, '\\-$1]');
            // helped needed to skip re-escaping regular expression
            $string->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::IS_REGULAR_PATTERN, \true);
        }
    }
}
