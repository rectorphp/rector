<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions;

use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions\TyposcriptConditionMatcher;
use Ssch\TYPO3Rector\Helper\ArrayUtility;
abstract class AbstractRootlineConditionMatcher implements \Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions\TyposcriptConditionMatcher
{
    public function change(string $condition) : ?string
    {
        \preg_match('#' . $this->getType() . self::ZERO_ONE_OR_MORE_WHITESPACES . '=' . self::ZERO_ONE_OR_MORE_WHITESPACES . '(.*)#', $condition, $matches);
        if (!\is_array($matches)) {
            return $condition;
        }
        $values = \Ssch\TYPO3Rector\Helper\ArrayUtility::trimExplode(',', $matches[1], \true);
        $newConditions = [];
        foreach ($values as $value) {
            $newConditions[] = \sprintf('%s in %s', $value, $this->getExpression());
        }
        return \implode(' || ', $newConditions);
    }
    public function shouldApply(string $condition) : bool
    {
        if (\strpos($condition, self::CONTAINS_CONSTANT) !== \false) {
            return \false;
        }
        return \strncmp($condition, $this->getType(), \strlen($this->getType())) === 0;
    }
    protected abstract function getType() : string;
    protected abstract function getExpression() : string;
}
