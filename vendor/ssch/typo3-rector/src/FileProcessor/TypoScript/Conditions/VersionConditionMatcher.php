<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions;

use RectorPrefix20210630\Nette\Utils\Strings;
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
        return \RectorPrefix20210630\Nette\Utils\Strings::startsWith($condition, self::TYPE);
    }
}
