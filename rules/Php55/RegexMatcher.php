<?php

declare (strict_types=1);
namespace Rector\Php55;

use RectorPrefix202308\Nette\Utils\Strings;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Scalar\String_;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
final class RegexMatcher
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var string
     * @see https://regex101.com/r/Ok4wuE/1
     */
    private const LAST_E_REGEX = '#(\\w+)?e(\\w+)?$#';
    /**
     * @var string
     * @see https://regex101.com/r/2NWVwT/1
     */
    private const LETTER_SUFFIX_REGEX = '#(?<modifiers>\\w+)$#';
    /**
     * @var string[]
     * @see https://www.php.net/manual/en/reference.pcre.pattern.modifiers.php
     */
    private const ALL_MODIFIERS_VALUES = ['i', 'm', 's', 'x', 'e', 'A', 'D', 'S', 'U', 'X', 'J', 'u'];
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\Concat|\PhpParser\Node\Scalar\String_|null
     */
    public function resolvePatternExpressionWithoutEIfFound(Expr $expr)
    {
        if ($expr instanceof String_) {
            $pattern = $this->valueResolver->getValue($expr);
            if (!\is_string($pattern)) {
                return null;
            }
            $delimiter = $pattern[0];
            switch ($delimiter) {
                case '(':
                    $delimiter = ')';
                    break;
                case '{':
                    $delimiter = '}';
                    break;
                case '[':
                    $delimiter = ']';
                    break;
                case '<':
                    $delimiter = '>';
                    break;
                default:
                    $delimiter = $delimiter;
                    break;
            }
            /** @var string $modifiers */
            $modifiers = $this->resolveModifiers((string) Strings::after($pattern, $delimiter, -1));
            if (\strpos($modifiers, 'e') === \false) {
                return null;
            }
            $patternWithoutE = $this->createPatternWithoutE($pattern, $delimiter, $modifiers);
            return new String_($patternWithoutE);
        }
        if ($expr instanceof Concat) {
            return $this->matchConcat($expr);
        }
        return null;
    }
    private function resolveModifiers(string $modifiersCandidate) : string
    {
        $modifiers = '';
        for ($modifierIndex = 0; $modifierIndex < \strlen($modifiersCandidate); ++$modifierIndex) {
            if (!\in_array($modifiersCandidate[$modifierIndex], self::ALL_MODIFIERS_VALUES, \true)) {
                $modifiers = '';
                continue;
            }
            $modifiers .= $modifiersCandidate[$modifierIndex];
        }
        return $modifiers;
    }
    private function createPatternWithoutE(string $pattern, string $delimiter, string $modifiers) : string
    {
        $modifiersWithoutE = Strings::replace($modifiers, '#e#');
        return Strings::before($pattern, $delimiter, -1) . $delimiter . $modifiersWithoutE;
    }
    private function matchConcat(Concat $concat) : ?Concat
    {
        $lastItem = $concat->right;
        if (!$lastItem instanceof String_) {
            return null;
        }
        $matches = Strings::match($lastItem->value, self::LETTER_SUFFIX_REGEX);
        if (!isset($matches['modifiers'])) {
            return null;
        }
        if (\strpos((string) $matches['modifiers'], 'e') === \false) {
            return null;
        }
        // replace last "e" in the code
        $lastItem->value = Strings::replace($lastItem->value, self::LAST_E_REGEX, '$1$2');
        return $concat;
    }
}
