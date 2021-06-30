<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions;

use RectorPrefix20210630\Nette\Utils\Strings;
use Ssch\TYPO3Rector\Helper\ArrayUtility;
final class GlobalStringConditionMatcher extends \Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\AbstractGlobalConditionMatcher
{
    /**
     * @var string
     */
    private const TYPE = 'globalString';
    public function change(string $condition) : ?string
    {
        \preg_match('#' . self::TYPE . self::ZERO_ONE_OR_MORE_WHITESPACES . '=' . self::ZERO_ONE_OR_MORE_WHITESPACES . '(?<subCondition>.*)#', $condition, $subConditions);
        if (!\is_string($subConditions['subCondition'])) {
            return $condition;
        }
        $subConditions = \Ssch\TYPO3Rector\Helper\ArrayUtility::trimExplode(',', $subConditions['subCondition']);
        $newConditions = [];
        foreach ($subConditions as $subCondition) {
            \preg_match('#(?<type>ENV|IENV|GP|TSFE|LIT)' . self::ZERO_ONE_OR_MORE_WHITESPACES . ':' . self::ZERO_ONE_OR_MORE_WHITESPACES . '(?<property>.*)\\s*(?<operator>' . self::ALLOWED_OPERATORS_REGEX . ')' . self::ZERO_ONE_OR_MORE_WHITESPACES . '(?<value>.*)$#Ui', $subCondition, $matches);
            $type = \trim($matches['type']);
            $property = \trim($matches['property']);
            $operator = \trim($matches['operator']);
            $value = \trim($matches['value']);
            if ('ENV' === $type) {
                $newConditions[] = $this->createEnvCondition($property, $operator, $value);
            } elseif ('IENV' === $type) {
                $newConditions[] = $this->createIndependentCondition($property, $operator, $value);
            } elseif ('TSFE' === $type) {
                $newConditions[] = $this->refactorTsfe($property, $operator, $value);
            } elseif ('GP' === $type) {
                $newConditions[] = $this->refactorGetPost($property, $operator, $value);
            } elseif ('LIT' === $type) {
                $newConditions[] = \sprintf('"%s" %s "%s"', $value, self::OPERATOR_MAPPING[$operator], $property);
            }
        }
        return \implode(' || ', $newConditions);
    }
    public function shouldApply(string $condition) : bool
    {
        if (\RectorPrefix20210630\Nette\Utils\Strings::contains($condition, self::CONTAINS_CONSTANT)) {
            return \false;
        }
        return \RectorPrefix20210630\Nette\Utils\Strings::startsWith($condition, self::TYPE);
    }
    private function refactorGetPost(string $property, string $operator, string $value) : string
    {
        $parameters = \Ssch\TYPO3Rector\Helper\ArrayUtility::trimExplode('|', $property);
        if (!\is_numeric($value)) {
            $value = \sprintf("'%s'", $value);
        }
        if (1 === \count($parameters)) {
            return \sprintf('request.getQueryParams()[\'%1$s\'] %2$s %3$s', $parameters[0], self::OPERATOR_MAPPING[$operator], $value);
        }
        return \sprintf('traverse(request.getQueryParams(), \'%1$s\') %2$s %3$s || traverse(request.getParsedBody(), \'%1$s\') %2$s %3$s', \implode('/', $parameters), self::OPERATOR_MAPPING[$operator], $value);
    }
}
