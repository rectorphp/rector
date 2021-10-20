<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions;

use RectorPrefix20211020\Nette\Utils\Strings;
use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions\TyposcriptConditionMatcher;
use Ssch\TYPO3Rector\Helper\ArrayUtility;
final class TimeConditionMatcher implements \Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions\TyposcriptConditionMatcher
{
    /**
     * @var string
     */
    private const ALLOWED_TIME_CONSTANTS = 'hour|minute|month|year|dayofweek|dayofmonth|dayofyear';
    /**
     * @var array<string, string>
     */
    private const TIME_MAPPING = ['hour' => 'G', 'minute' => 'i', 'month' => 'm', 'year' => 'Y', 'dayofweek' => 'w', 'dayofmonth' => 'j', 'dayofyear' => 'z'];
    /**
     * @param string $condition
     */
    public function change($condition) : ?string
    {
        \preg_match('#^(?<time>' . self::ALLOWED_TIME_CONSTANTS . ')' . self::ZERO_ONE_OR_MORE_WHITESPACES . '=' . self::ZERO_ONE_OR_MORE_WHITESPACES . '(?<operatorsAndValues>.*)$#Ui', $condition, $matches);
        if (!\is_array($matches)) {
            return $condition;
        }
        $time = $matches['time'] ?? '';
        $value = $matches['operatorsAndValues'] ?? '';
        $newConditions = [];
        $operatorsAndValues = \Ssch\TYPO3Rector\Helper\ArrayUtility::trimExplode(',', $value, \true);
        foreach ($operatorsAndValues as $operatorAndValue) {
            \preg_match('#(?<operator>' . self::ALLOWED_OPERATORS_REGEX . '|\\b)' . self::ZERO_ONE_OR_MORE_WHITESPACES . '(?<value>.*)$#Ui', $operatorAndValue, $operatorAndValueMatches);
            if (!\is_array($operatorAndValueMatches)) {
                continue;
            }
            $operator = $operatorAndValueMatches['operator'] ?? '';
            $value = $operatorAndValueMatches['value'] ?? '';
            if ('' === $operator) {
                $operator = '=';
            }
            $newConditions[] = \sprintf('date("%s") %s %s', \trim(self::TIME_MAPPING[$time]), \trim(self::OPERATOR_MAPPING[$operator]), \trim($value));
        }
        return \implode(' || ', $newConditions);
    }
    /**
     * @param string $condition
     */
    public function shouldApply($condition) : bool
    {
        return null !== \RectorPrefix20211020\Nette\Utils\Strings::match($condition, '#' . self::ALLOWED_TIME_CONSTANTS . '#Ui');
    }
}
