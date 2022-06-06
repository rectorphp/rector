<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions;

use RectorPrefix20220606\Nette\Utils\Strings;
use RectorPrefix20220606\Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions\TyposcriptConditionMatcher;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\ArrayUtility;
final class TimeConditionMatcher implements TyposcriptConditionMatcher
{
    /**
     * @var string
     */
    private const ALLOWED_TIME_CONSTANTS = 'hour|minute|month|year|dayofweek|dayofmonth|dayofyear';
    /**
     * @var array<string, string>
     */
    private const TIME_MAPPING = ['hour' => 'G', 'minute' => 'i', 'month' => 'm', 'year' => 'Y', 'dayofweek' => 'w', 'dayofmonth' => 'j', 'dayofyear' => 'z'];
    public function change(string $condition) : ?string
    {
        \preg_match('#^(?<time>' . self::ALLOWED_TIME_CONSTANTS . ')' . self::ZERO_ONE_OR_MORE_WHITESPACES . '=' . self::ZERO_ONE_OR_MORE_WHITESPACES . '(?<operatorsAndValues>.*)$#Ui', $condition, $matches);
        if (!\is_array($matches)) {
            return $condition;
        }
        $time = $matches['time'] ?? '';
        $value = $matches['operatorsAndValues'] ?? '';
        $newConditions = [];
        $operatorsAndValues = ArrayUtility::trimExplode(',', $value, \true);
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
    public function shouldApply(string $condition) : bool
    {
        return null !== Strings::match($condition, '#' . self::ALLOWED_TIME_CONSTANTS . '#Ui');
    }
}
