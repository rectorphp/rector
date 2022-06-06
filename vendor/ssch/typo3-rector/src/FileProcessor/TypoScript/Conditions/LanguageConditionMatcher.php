<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions;

use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions\TyposcriptConditionMatcher;
final class LanguageConditionMatcher implements \Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions\TyposcriptConditionMatcher
{
    /**
     * @var string
     */
    private const TYPE = 'language';
    public function change(string $condition) : ?string
    {
        \preg_match('#^' . self::TYPE . '\\s*=\\s*(?<value>.*)$#iUm', $condition, $matches);
        if (!\is_string($matches['value'])) {
            return $condition;
        }
        return \sprintf('siteLanguage("twoLetterIsoCode") == "%s"', \trim($matches['value']));
    }
    public function shouldApply(string $condition) : bool
    {
        return \strncmp($condition, self::TYPE, \strlen(self::TYPE)) === 0;
    }
}
