<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions;

use RectorPrefix20210710\Nette\Utils\Strings;
use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions\TyposcriptConditionMatcher;
final class BrowserConditionMatcher implements \Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions\TyposcriptConditionMatcher
{
    /**
     * @var string
     */
    private const TYPE = 'browser';
    /**
     * @param string $condition
     */
    public function change($condition) : ?string
    {
        return $condition;
    }
    /**
     * @param string $condition
     */
    public function shouldApply($condition) : bool
    {
        return \RectorPrefix20210710\Nette\Utils\Strings::startsWith($condition, self::TYPE);
    }
}
