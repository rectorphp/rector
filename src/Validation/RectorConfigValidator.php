<?php

declare (strict_types=1);
namespace Rector\Validation;

use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Contract\Rector\RectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
final class RectorConfigValidator
{
    /**
     * @param string[] $rectorClasses
     */
    public static function ensureNoDuplicatedClasses(array $rectorClasses): void
    {
        $duplicatedRectorClasses = self::resolveDuplicatedValues($rectorClasses);
        if ($duplicatedRectorClasses === []) {
            return;
        }
        throw new ShouldNotHappenException('Following rules are registered twice: ' . implode(', ', $duplicatedRectorClasses));
    }
    /**
     * @param mixed[] $skip
     */
    public static function ensureRectorRulesExist(array $skip): void
    {
        $nonExistingRules = [];
        $skippedRectorRules = [];
        $skippedNonRectorClasses = [];
        foreach ($skip as $key => $value) {
            if (is_string($key) && self::isNonRectorClass($key)) {
                $skippedNonRectorClasses[] = $key;
                continue;
            }
            if (self::isRectorClassValue($key)) {
                if (class_exists($key)) {
                    $skippedRectorRules[] = $key;
                } else {
                    $nonExistingRules[] = $key;
                }
                continue;
            }
            if (!self::isRectorClassValue($value)) {
                continue;
            }
            if (class_exists($value)) {
                $skippedRectorRules[] = $value;
                continue;
            }
            $nonExistingRules[] = $value;
        }
        SimpleParameterProvider::addParameter(Option::SKIPPED_RECTOR_RULES, $skippedRectorRules);
        SimpleParameterProvider::addParameter(Option::SKIPPED_NON_RECTOR_CLASSES, $skippedNonRectorClasses);
        if ($nonExistingRules === []) {
            return;
        }
        $nonExistingRulesString = '';
        foreach ($nonExistingRules as $nonExistingRule) {
            $nonExistingRulesString .= ' * ' . $nonExistingRule . \PHP_EOL;
        }
        throw new ShouldNotHappenException('These rules from "$rectorConfig->skip()" do not exist - remove them or fix their names:' . \PHP_EOL . $nonExistingRulesString);
    }
    /**
     * Only Rector rules are matched against skipped classes, so any other class can never be skipped
     */
    private static function isNonRectorClass(string $key): bool
    {
        // interfaces are allowed, as they can mark a group of Rector rules
        if (!class_exists($key)) {
            return \false;
        }
        if (is_a($key, RectorInterface::class, \true)) {
            return \false;
        }
        return !is_a($key, PostRectorInterface::class, \true);
    }
    /**
     * @param mixed $value
     */
    private static function isRectorClassValue($value): bool
    {
        // only validate string
        if (!is_string($value)) {
            return \false;
        }
        // not regex path
        if (strpos($value, '*') !== \false) {
            return \false;
        }
        // not if no Rector suffix
        if (substr_compare($value, 'Rector', -strlen('Rector')) !== 0) {
            return \false;
        }
        // not directory
        if (is_dir($value)) {
            return \false;
        }
        // not file
        return !is_file($value);
    }
    /**
     * @param string[] $values
     * @return string[]
     */
    private static function resolveDuplicatedValues(array $values): array
    {
        $counted = array_count_values($values);
        $duplicates = [];
        foreach ($counted as $value => $count) {
            if ($count > 1) {
                $duplicates[] = $value;
            }
        }
        return array_unique($duplicates);
    }
}
