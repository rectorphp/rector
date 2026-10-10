<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\String_;

use RectorPrefix202610\Nette\Utils\Validators;
use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\Util\StringUtils;
/**
 * @see \Rector\Tests\CodingStyle\Rector\String_\SimplifyQuoteEscapeRector\SimplifyQuoteEscapeRectorTest
 */
final class SimplifyQuoteEscapeRector extends AbstractRector
{
    /**
     * @see https://regex101.com/r/qEkCe9/2
     * @var string
     */
    private const ESCAPED_CHAR_REGEX = '#\\\\|\$|\n|\t#sim';
    /**
     * @see https://alvinalexander.com/php/how-to-remove-non-printable-characters-in-string-regex/
     * @see https://regex101.com/r/lGUhRb/1
     * @var string
     */
    private const HAS_NON_PRINTABLE_CHARS = '#[\x00-\x1F\x80-\xFF]#';
    /**
     * @see https://regex101.com/r/tY7eKW/1
     * @var string
     */
    private const CONTROL_CHARS_REGEX = '#[\x00-\x1F]#';
    private bool $hasChanged = \false;
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Prefer quote that are not inside the string', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
         $name = "\" Tom";
         $name = '\' Sara';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
         $name = '" Tom';
         $name = "' Sara";
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [String_::class];
    }
    /**
     * @param String_ $node
     */
    public function refactor(Node $node): ?String_
    {
        $this->hasChanged = \false;
        if ($this->hasNonPrintableChars($node->value)) {
            return null;
        }
        $doubleQuoteCount = substr_count($node->value, '"');
        $singleQuoteCount = substr_count($node->value, "'");
        $kind = $node->getAttribute(AttributeKey::KIND);
        if ($kind === String_::KIND_SINGLE_QUOTED) {
            $this->processSingleQuoted($node, $doubleQuoteCount, $singleQuoteCount);
        }
        $quoteKind = $node->getAttribute(AttributeKey::KIND);
        if ($quoteKind === String_::KIND_DOUBLE_QUOTED) {
            $this->processDoubleQuoted($node, $singleQuoteCount, $doubleQuoteCount);
        }
        if (!$this->hasChanged) {
            return null;
        }
        return $node;
    }
    private function processSingleQuoted(String_ $string, int $doubleQuoteCount, int $singleQuoteCount): void
    {
        if ($doubleQuoteCount === 0 && $singleQuoteCount > 0) {
            // contains chars that will be newly escaped
            if ($this->isMatchEscapedChars($string->value)) {
                return;
            }
            $string->setAttribute(AttributeKey::KIND, String_::KIND_DOUBLE_QUOTED);
            // invoke override
            $string->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            $this->hasChanged = \true;
        }
    }
    private function processDoubleQuoted(String_ $string, int $singleQuoteCount, int $doubleQuoteCount): void
    {
        if ($singleQuoteCount === 0 && $doubleQuoteCount > 0) {
            // contains chars that will be newly escaped
            if ($this->isMatchEscapedChars($string->value)) {
                return;
            }
            // contains escape sequence that will be printed as raw char, e.g. "\u{00A0}"
            if ($this->hasEscapeSequence($string)) {
                return;
            }
            $string->setAttribute(AttributeKey::KIND, String_::KIND_SINGLE_QUOTED);
            // invoke override
            $string->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            $this->hasChanged = \true;
        }
    }
    private function isMatchEscapedChars(string $string): bool
    {
        return StringUtils::isMatch($string, self::ESCAPED_CHAR_REGEX);
    }
    private function hasEscapeSequence(String_ $string): bool
    {
        $rawValue = $string->getAttribute(AttributeKey::RAW_VALUE);
        if (!is_string($rawValue)) {
            return \false;
        }
        // any escape sequence other than \" makes the written content differ from the value
        return str_replace('\"', '"', (string) substr($rawValue, 1, -1)) !== $string->value;
    }
    private function hasNonPrintableChars(string $value): bool
    {
        if (!StringUtils::isMatch($value, self::HAS_NON_PRINTABLE_CHARS)) {
            return \false;
        }
        if (StringUtils::isMatch($value, self::CONTROL_CHARS_REGEX)) {
            return \true;
        }
        // bytes above ASCII are printable as part of valid UTF-8 text, e.g. "é"
        return !Validators::isUnicode($value);
    }
}
