<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\TypoScript\Conditions;

use RectorPrefix20210522\Nette\Utils\Strings;
use Ssch\TYPO3Rector\Contract\TypoScript\Conditions\TyposcriptConditionMatcher;
use Ssch\TYPO3Rector\Helper\ArrayUtility;
final class TreeLevelConditionMatcher implements \Ssch\TYPO3Rector\Contract\TypoScript\Conditions\TyposcriptConditionMatcher
{
    /**
     * @var string
     */
    private const TYPE = 'treeLevel';
    public function change(string $condition) : ?string
    {
        \preg_match('#' . self::TYPE . '\\s*=\\s*(.*)#', $condition, $matches);
        if (!\is_array($matches)) {
            return $condition;
        }
        $values = \Ssch\TYPO3Rector\Helper\ArrayUtility::trimExplode(',', $matches[1], \true);
        return \sprintf('tree.level in [%s]', \implode(',', $values));
    }
    public function shouldApply(string $condition) : bool
    {
        if (\RectorPrefix20210522\Nette\Utils\Strings::contains($condition, self::CONTAINS_CONSTANT)) {
            return \false;
        }
        return \RectorPrefix20210522\Nette\Utils\Strings::startsWith($condition, self::TYPE);
    }
}
