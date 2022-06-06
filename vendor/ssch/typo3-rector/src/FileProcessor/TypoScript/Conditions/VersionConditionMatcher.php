<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions;

use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions\TyposcriptConditionMatcher;
final class VersionConditionMatcher implements \Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions\TyposcriptConditionMatcher
{
    /**
     * @var string
     */
    private const TYPE = 'version';
    public function change(string $condition) : ?string
    {
        return $condition;
    }
    public function shouldApply(string $condition) : bool
    {
        return \strncmp($condition, self::TYPE, \strlen(self::TYPE)) === 0;
    }
}
