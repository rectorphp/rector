<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\FuncCall;

use RectorPrefix202305\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\PhpParser\NodePrinterInterface;
use Rector\Core\Contract\Rector\AllowEmptyConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\StringUtils;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector\ConsistentPregDelimiterRectorTest
 */
final class ConsistentPregDelimiterRector extends AbstractRector implements AllowEmptyConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const DELIMITER = 'delimiter';
    /**
     * @var string
     * @see https://regex101.com/r/isdgEN/1
     *
     * For modifiers see https://www.php.net/manual/en/reference.pcre.pattern.modifiers.php
     */
    private const INNER_REGEX = '#(?<content>.*?)(?<close>[imsxeADSUXJu]*)$#s';
    /**
     * @var string
     * @see https://regex101.com/r/nnuwUo/1
     *
     * For modifiers see https://www.php.net/manual/en/reference.pcre.pattern.modifiers.php
     */
    private const INNER_UNICODE_REGEX = '#(?<content>.*?)(?<close>[imsxeADSUXJu]*)$#u';
    /**
     * @var string
     * @see https://regex101.com/r/KpCzg6/1
     */
    private const NEW_LINE_REGEX = '#(\\r|\\n)#';
    /**
     * @var string
     * @see https://regex101.com/r/EyXsV6/6
     */
    private const DOUBLE_QUOTED_REGEX = '#^"(?<delimiter>.{1}).{1,}\\k<delimiter>[imsxeADSUXJu]*"$#';
    /**
     * All with pattern as 1st argument
     * @var array<string, int>
     */
    private const FUNCTIONS_WITH_REGEX_PATTERN = ['preg_match' => 0, 'preg_replace_callback_array' => 0, 'preg_replace_callback' => 0, 'preg_replace' => 0, 'preg_match_all' => 0, 'preg_split' => 0, 'preg_grep' => 0];
    /**
     * All with pattern as 2st argument
     * @var array<string, array<string, int>>
     */
    private const STATIC_METHODS_WITH_REGEX_PATTERN = ['Nette\\Utils\\Strings' => ['match' => 1, 'matchAll' => 1, 'replace' => 1, 'split' => 1]];
    /**
     * @var string
     */
    private $delimiter = '#';
    /**
     * @readonly
     * @var \Rector\Core\Contract\PhpParser\NodePrinterInterface
     */
    private $nodePrinter;
    public function __construct(NodePrinterInterface $nodePrinter)
    {
        $this->nodePrinter = $nodePrinter;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace PREG delimiter with configured one', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        preg_match('~value~', $value);
        preg_match_all('~value~im', $value);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        preg_match('#value#', $value);
        preg_match_all('#value#im', $value);
    }
}
CODE_SAMPLE
, [self::DELIMITER => '#'])]);
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
        if ($node instanceof FuncCall) {
            return $this->refactorFuncCall($node);
        }
        foreach (self::STATIC_METHODS_WITH_REGEX_PATTERN as $type => $methodsToPositions) {
            if (!$this->isObjectType($node->class, new ObjectType($type))) {
                continue;
            }
            foreach ($methodsToPositions as $method => $position) {
                if (!$this->isName($node->name, $method)) {
                    continue;
                }
                if (!$node->args[$position] instanceof Arg) {
                    continue;
                }
                return $this->refactorArgument($node, $node->args[$position]);
            }
        }
        return null;
    }
    public function configure(array $configuration) : void
    {
        $this->delimiter = $configuration[self::DELIMITER] ?? (string) \current($configuration);
    }
    private function refactorFuncCall(FuncCall $funcCall) : ?FuncCall
    {
        foreach (self::FUNCTIONS_WITH_REGEX_PATTERN as $function => $position) {
            if (!$this->isName($funcCall, $function)) {
                continue;
            }
            if (!$funcCall->args[$position] instanceof Arg) {
                continue;
            }
            return $this->refactorArgument($funcCall, $funcCall->args[$position]);
        }
        return null;
    }
    private function hasNewLineWithUnicodeModifier(string $string) : bool
    {
        $matchInnerRegex = Strings::match($string, self::INNER_REGEX);
        $matchInnerUnionRegex = Strings::match($string, self::INNER_UNICODE_REGEX);
        if (!\is_array($matchInnerRegex)) {
            return \false;
        }
        if (!\is_array($matchInnerUnionRegex)) {
            return \false;
        }
        if ($matchInnerRegex === $matchInnerUnionRegex) {
            return \false;
        }
        if (StringUtils::isMatch($matchInnerUnionRegex['content'], self::NEW_LINE_REGEX)) {
            return \true;
        }
        return isset($string[0]) && $matchInnerUnionRegex['content'] === $string[0];
    }
    private function hasEscapedQuote(String_ $string) : bool
    {
        $kind = $string->getAttribute(AttributeKey::KIND);
        if ($kind === String_::KIND_DOUBLE_QUOTED && \strpos($string->value, '"') !== \false) {
            return \true;
        }
        return $kind === String_::KIND_SINGLE_QUOTED && \strpos($string->value, "'") !== \false;
    }
    /**
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function refactorArgument($node, Arg $arg) : ?\PhpParser\Node
    {
        if (!$arg->value instanceof String_) {
            return null;
        }
        /** @var String_ $string */
        $string = $arg->value;
        if ($this->hasEscapedQuote($string)) {
            return null;
        }
        if ($this->hasNewLineWithUnicodeModifier($string->value)) {
            return null;
        }
        $string->value = Strings::replace($string->value, self::INNER_REGEX, function (array $match) use(&$string) : string {
            $printedString = $this->nodePrinter->print($string);
            if (StringUtils::isMatch($printedString, self::DOUBLE_QUOTED_REGEX)) {
                $string->setAttribute(AttributeKey::IS_REGULAR_PATTERN, \true);
            }
            $innerPattern = $match['content'];
            $positionDelimiter = \strpos($innerPattern, $this->delimiter);
            if ($positionDelimiter > 0) {
                $innerPattern = \str_replace($this->delimiter, '\\' . $this->delimiter, $innerPattern);
            }
            // change delimiter
            if (\strlen($innerPattern) > 2 && $innerPattern[0] === $innerPattern[\strlen($innerPattern) - 1]) {
                $innerPattern[0] = $this->delimiter;
                $innerPattern[\strlen($innerPattern) - 1] = $this->delimiter;
            }
            return $innerPattern . $match['close'];
        });
        return $node;
    }
}
