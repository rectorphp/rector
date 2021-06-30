<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions;

use RectorPrefix20210630\Nette\Utils\Strings;
use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions\TyposcriptConditionMatcher;
final class AdminUserConditionMatcher implements \Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions\TyposcriptConditionMatcher
{
    /**
     * @var string
     */
    private const TYPE = 'adminUser';
    public function change(string $condition) : ?string
    {
        \preg_match('#^' . self::TYPE . '\\s*=\\s*(?<value>[0-1])$#iUm', $condition, $matches);
        if (!\is_string($matches['value'])) {
            return $condition;
        }
        $value = (int) $matches['value'];
        if (1 === $value) {
            return 'backend.user.isAdmin';
        }
        return 'backend.user.isAdmin == 0';
    }
    public function shouldApply(string $condition) : bool
    {
        return \RectorPrefix20210630\Nette\Utils\Strings::startsWith($condition, self::TYPE);
    }
}
