<?php

declare(strict_types=1);

namespace Rector\Php73\Rector\FuncCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Php\Regex\RegexPatternArgumentManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://3v4l.org/dRG8U
 * @see \Rector\Php73\Tests\Rector\FuncCall\RegexDashEscapeRector\RegexDashEscapeRectorTest
 */
final class RegexDashEscapeRector extends AbstractRector
{
    /**
     * @var string
     * @see https://regex101.com/r/YgVJFp/1
     */
    private const LEFT_HAND_UNESCAPED_DASH_REGEX = '#(\[.*?\\\\(w|s|d))-(?!\])#i';

    /**
     * @var string
     * @see https://regex101.com/r/TBVme9/3
     */
    private const RIGHT_HAND_UNESCAPED_DASH_REGEX = '#(?<!\[)-(\\\\(w|s|d)[^\?]*?)\]#i';

    /**
     * @var RegexPatternArgumentManipulator
     */
    private $regexPatternArgumentManipulator;

    public function __construct(RegexPatternArgumentManipulator $regexPatternArgumentManipulator)
    {
        $this->regexPatternArgumentManipulator = $regexPatternArgumentManipulator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Escape - in some cases', [
            new CodeSample(
                <<<'CODE_SAMPLE'
preg_match("#[\w-()]#", 'some text');
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
preg_match("#[\w\-()]#", 'some text');
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class, StaticCall::class];
    }

    /**
     * @param FuncCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $regexArguments = $this->regexPatternArgumentManipulator->matchCallArgumentWithRegexPattern($node);
        if ($regexArguments === []) {
            return null;
        }

        foreach ($regexArguments as $regexArgument) {
            $this->escapeStringNode($regexArgument);
        }

        return $node;
    }

    private function escapeStringNode(String_ $string): void
    {
        $stringValue = $string->value;

        if (Strings::match($stringValue, self::LEFT_HAND_UNESCAPED_DASH_REGEX)) {
            $string->value = Strings::replace($stringValue, self::LEFT_HAND_UNESCAPED_DASH_REGEX, '$1\-');
            // helped needed to skip re-escaping regular expression
            $string->setAttribute(AttributeKey::IS_REGULAR_PATTERN, true);
            return;
        }

        if (Strings::match($stringValue, self::RIGHT_HAND_UNESCAPED_DASH_REGEX)) {
            $string->value = Strings::replace($stringValue, self::RIGHT_HAND_UNESCAPED_DASH_REGEX, '\-$1]');
            // helped needed to skip re-escaping regular expression
            $string->setAttribute(AttributeKey::IS_REGULAR_PATTERN, true);
        }
    }
}
