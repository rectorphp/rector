<?php

declare (strict_types=1);
namespace Rector\Php55;

use RectorPrefix202611\Nette\Utils\Strings;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\InterpolatedStringPart;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\Node\Scalar\String_;
final class RegexMatcher
{
    /**
     * @see https://regex101.com/r/Ok4wuE/1
     * @var string
     */
    private const LAST_E_REGEX = '#(\w+)?e(\w+)?$#';
    /**
     * @see https://regex101.com/r/2NWVwT/1
     * @var string
     */
    private const LETTER_SUFFIX_REGEX = '#(?<modifiers>\w+)$#';
    /**
     * @var string[]
     * @see https://www.php.net/manual/en/reference.pcre.pattern.modifiers.php
     */
    private const ALL_MODIFIERS_VALUES = ['i', 'm', 's', 'x', 'e', 'A', 'D', 'S', 'U', 'X', 'J', 'u'];
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\Concat|\PhpParser\Node\Scalar\InterpolatedString|\PhpParser\Node\Scalar\String_|null
     */
    public function resolvePatternExpressionWithoutEIfFound(Expr $expr)
    {
        if ($expr instanceof String_) {
            $pattern = $expr->value;
            $delimiter = $this->resolveClosingDelimiter($pattern);
            $modifiers = $this->resolveModifiers((string) Strings::after($pattern, $delimiter, -1));
            if (strpos($modifiers, 'e') === \false) {
                return null;
            }
            $expr->value = $this->createPatternWithoutE($pattern, $delimiter, $modifiers);
            return $expr;
        }
        if ($expr instanceof Concat) {
            return $this->matchConcat($expr);
        }
        if ($expr instanceof InterpolatedString) {
            return $this->matchInterpolatedString($expr);
        }
        return null;
    }
    private function resolveClosingDelimiter(string $pattern): string
    {
        $delimiter = $pattern[0];
        switch ($delimiter) {
            case '(':
                return ')';
            case '{':
                return '}';
            case '[':
                return ']';
            case '<':
                return '>';
            default:
                return $delimiter;
        }
    }
    private function matchInterpolatedString(InterpolatedString $interpolatedString): ?InterpolatedString
    {
        $firstPart = $interpolatedString->parts[0] ?? null;
        if (!$firstPart instanceof InterpolatedStringPart) {
            return null;
        }
        $lastPart = $interpolatedString->parts[array_key_last($interpolatedString->parts)];
        if (!$lastPart instanceof InterpolatedStringPart) {
            return null;
        }
        $delimiter = $this->resolveClosingDelimiter($firstPart->value);
        $pattern = $lastPart->value;
        $modifiers = $this->resolveModifiers((string) Strings::after($pattern, $delimiter, -1));
        if (strpos($modifiers, 'e') === \false) {
            return null;
        }
        $lastPart->value = $this->createPatternWithoutE($pattern, $delimiter, $modifiers);
        return $interpolatedString;
    }
    private function resolveModifiers(string $modifiersCandidate): string
    {
        $modifiers = '';
        for ($modifierIndex = 0; $modifierIndex < strlen($modifiersCandidate); ++$modifierIndex) {
            if (!in_array($modifiersCandidate[$modifierIndex], self::ALL_MODIFIERS_VALUES, \true)) {
                $modifiers = '';
                continue;
            }
            $modifiers .= $modifiersCandidate[$modifierIndex];
        }
        return $modifiers;
    }
    private function createPatternWithoutE(string $pattern, string $delimiter, string $modifiers): string
    {
        $modifiersWithoutE = str_replace('e', '', $modifiers);
        return Strings::before($pattern, $delimiter, -1) . $delimiter . $modifiersWithoutE;
    }
    private function matchConcat(Concat $concat): ?Concat
    {
        // cause parse error
        if (!$concat->left instanceof Concat) {
            return null;
        }
        $lastItem = $concat->right;
        if (!$lastItem instanceof String_) {
            return null;
        }
        $matches = Strings::match($lastItem->value, self::LETTER_SUFFIX_REGEX);
        if (!isset($matches['modifiers'])) {
            return null;
        }
        if (strpos($matches['modifiers'], 'e') === \false) {
            return null;
        }
        // replace last "e" in the code
        $lastItem->value = Strings::replace($lastItem->value, self::LAST_E_REGEX, '$1$2');
        return $concat;
    }
}
