<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions;

use Rector\Core\Exception\ShouldNotHappenException;
use Ssch\TYPO3Rector\Helper\ArrayUtility;
final class GlobalVarConditionMatcher extends \Ssch\TYPO3Rector\FileProcessor\TypoScript\Conditions\AbstractGlobalConditionMatcher
{
    /**
     * @var string
     */
    private const TYPE = 'globalVar';
    /**
     * @var string
     */
    private const VALUE = 'value';
    public function change(string $condition) : ?string
    {
        \preg_match('#' . self::TYPE . '\\s*=\\s*(?<subCondition>.*)#', $condition, $subConditions);
        if (!\is_string($subConditions['subCondition'])) {
            return $condition;
        }
        $subConditions = \Ssch\TYPO3Rector\Helper\ArrayUtility::trimExplode(',', $subConditions['subCondition']);
        $conditions = [];
        foreach ($subConditions as $subCondition) {
            \preg_match('#(?<type>TSFE|GP|GPmerged|_POST|_GET|LIT|ENV|IENV|BE_USER)' . self::ZERO_ONE_OR_MORE_WHITESPACES . '[:|]' . self::ZERO_ONE_OR_MORE_WHITESPACES . '(?<property>.*)\\s*(?<operator>' . self::ALLOWED_OPERATORS_REGEX . ')' . self::ZERO_ONE_OR_MORE_WHITESPACES . '(?<value>.*)$#Ui', $subCondition, $matches);
            if (!\is_array($matches)) {
                continue;
            }
            $type = isset($matches['type']) ? \trim($matches['type']) : '';
            $property = isset($matches['property']) ? \trim($matches['property']) : '';
            $operator = isset($matches['operator']) ? \trim($matches['operator']) : '';
            $value = isset($matches[self::VALUE]) ? \trim($matches[self::VALUE]) : '';
            $key = \sprintf('%s.%s.%s', $type, $property, $operator);
            if (!isset($conditions[$key])) {
                $conditions[$key] = [];
            }
            switch ($type) {
                case 'TSFE':
                    $conditions[$key][] = $this->refactorTsfe($property, $operator, $value);
                    break;
                case 'GP':
                    $conditions[$key][] = $this->refactorGetPost($property, $operator, $value);
                    break;
                case 'LIT':
                    $conditions[$key][] = \sprintf('"%s" %s "%s"', $value, self::OPERATOR_MAPPING[$operator], $property);
                    break;
                case 'ENV':
                    $conditions[$key][] = $this->createEnvCondition($property, $operator, $value);
                    break;
                case 'IENV':
                    $conditions[$key][] = $this->createIndependentCondition($property, $operator, $value);
                    break;
                case 'BE_USER':
                    $conditions[$key][] = $this->createBackendUserCondition($property, $operator, $value);
                    break;
                case '_GET':
                    $conditions[$key][] = $this->refactorGet($property, $operator, $value);
                    break;
                case 'GPmerged':
                    $conditions[$key][] = $this->refactorGetPost($property, $operator, $value);
                    break;
                case '_POST':
                    $conditions[$key][] = $this->refactorPost($property, $operator, $value);
                    break;
                default:
                    $conditions[$key][] = $condition;
                    break;
            }
        }
        $keys = \array_keys($conditions);
        $newConditions = [];
        foreach ($keys as $key) {
            [, , $operator] = \explode('.', $key);
            if ('=' === $operator && (\is_array($conditions[$key]) || $conditions[$key] instanceof \Countable) && \count($conditions[$key]) > 1) {
                $values = [];
                $condition = '';
                foreach ($conditions[$key] as $value) {
                    \preg_match('#(?<condition>.*)\\s*==\\s*(?<value>.*)#', $value, $valueMatches);
                    if (!\is_array($valueMatches)) {
                        continue;
                    }
                    $values[] = $valueMatches[self::VALUE];
                    $condition = $valueMatches['condition'];
                }
                $newConditions[] = \sprintf('%s in [%s]', \trim($condition), \trim(\implode(',', $values)));
            } else {
                $newConditions[] = \implode(' || ', $conditions[$key]);
            }
        }
        return \implode(' || ', $newConditions);
    }
    public function shouldApply(string $condition) : bool
    {
        return \strncmp($condition, self::TYPE, \strlen(self::TYPE)) === 0;
    }
    private function refactorGetPost(string $property, string $operator, string $value) : string
    {
        if ('L' === $property) {
            return \sprintf('siteLanguage("languageId") %s "%s"', self::OPERATOR_MAPPING[$operator], $value);
        }
        $normalizedValue = $this->normalizeValue($value);
        $parameters = $this->explodeParameters($property);
        if (1 === \count($parameters)) {
            return \sprintf('request.getQueryParams()[\'%1$s\'] %2$s %3$s', $parameters[0], self::OPERATOR_MAPPING[$operator], $normalizedValue);
        }
        if ('_POST' === $property) {
            return \sprintf('traverse(request.getParsedBody(), \'%1$s\') %2$s %3$s) %2$s %3$s', \implode('/', $parameters), self::OPERATOR_MAPPING[$operator], $normalizedValue);
        }
        return \sprintf('traverse(request.getQueryParams(), \'%1$s\') %2$s %3$s || traverse(request.getParsedBody(), \'%1$s\') %2$s %3$s', \implode('/', $parameters), self::OPERATOR_MAPPING[$operator], $normalizedValue);
    }
    private function createBackendUserCondition(string $property, string $operator, string $value) : string
    {
        $delimiter = \strpos($property, ':') !== \false ? ':' : '|';
        [, $property] = \Ssch\TYPO3Rector\Helper\ArrayUtility::trimExplode($delimiter, $property, \true, 2);
        if (!\array_key_exists($property, self::USER_PROPERTY_MAPPING)) {
            $message = \sprintf('The property "%s" can not be mapped for condition BE_USER', $property);
            throw new \Rector\Core\Exception\ShouldNotHappenException($message);
        }
        return \sprintf('backend.user.%s %s %s', self::USER_PROPERTY_MAPPING[$property], self::OPERATOR_MAPPING[$operator], $value);
    }
    private function refactorGet(string $property, string $operator, string $value) : string
    {
        $normalizedValue = $this->normalizeValue($value);
        $parameters = $this->explodeParameters($property);
        return \sprintf('traverse(request.getQueryParams(), \'%1$s\') %2$s %3$s)', \implode('/', $parameters), self::OPERATOR_MAPPING[$operator], $normalizedValue);
    }
    private function refactorPost(string $property, string $operator, string $value) : string
    {
        $normalizedValue = $this->normalizeValue($value);
        $parameters = $this->explodeParameters($property);
        return \sprintf('traverse(request.getParsedBody(), \'%1$s\') %2$s %3$s)', \implode('/', $parameters), self::OPERATOR_MAPPING[$operator], $normalizedValue);
    }
    /**
     * @param int|string $value
     * @return int|string
     */
    private function normalizeValue($value)
    {
        if (!\is_numeric($value)) {
            return \sprintf("'%s'", $value);
        }
        return $value;
    }
    /**
     * @return string[]
     */
    private function explodeParameters(string $property) : array
    {
        return \Ssch\TYPO3Rector\Helper\ArrayUtility::trimExplode('|', $property);
    }
}
