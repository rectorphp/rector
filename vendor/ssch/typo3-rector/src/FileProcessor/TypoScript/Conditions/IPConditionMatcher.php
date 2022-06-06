<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions;

use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions\TyposcriptConditionMatcher;
use Ssch\TYPO3Rector\Helper\ArrayUtility;
final class IPConditionMatcher implements \Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions\TyposcriptConditionMatcher
{
    /**
     * @var string
     */
    private const TYPE = 'IP';
    public function change(string $condition) : ?string
    {
        \preg_match('#' . self::TYPE . '\\s*=\\s*(.*)#', $condition, $matches);
        if (!\is_array($matches)) {
            return $condition;
        }
        $values = \Ssch\TYPO3Rector\Helper\ArrayUtility::trimExplode(',', $matches[1], \true);
        return \sprintf('ip("%s")', \implode(',', $values));
    }
    public function shouldApply(string $condition) : bool
    {
        if (\strpos($condition, self::CONTAINS_CONSTANT) !== \false) {
            return \false;
        }
        return \strncmp($condition, self::TYPE, \strlen(self::TYPE)) === 0;
    }
}
