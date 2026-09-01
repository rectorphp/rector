<?php

declare (strict_types=1);
namespace Rector\Configuration\Parameter;

use Rector\Configuration\Option;
use Rector\Exception\ShouldNotHappenException;
use RectorPrefix202609\Webmozart\Assert\Assert;
/**
 * @api
 */
final class SimpleParameterProvider
{
    /**
     * Parameters that never change the refactored output - runtime tuning and reporting only.
     * They are excluded from the cache invalidation hash, so toggling e.g. parallel or memory
     * limit does not drop the whole cache.
     *
     * @var array<Option::*>
     */
    private const CACHE_IGNORED_PARAMETER_NAMES = [Option::PARALLEL, Option::PARALLEL_JOB_SIZE, Option::PARALLEL_MAX_NUMBER_OF_PROCESSES, Option::PARALLEL_JOB_TIMEOUT_IN_SECONDS, Option::MEMORY_LIMIT, Option::NO_DIFFS, Option::CACHE_DIR, Option::CONTAINER_CACHE_DIRECTORY, Option::EDITOR_URL, Option::ABSOLUTE_FILE_PATH, Option::REPORT_UNUSED_SKIPS, Option::IS_RECTORCONFIG_BUILDER_RECREATED, Option::IS_RUN_NARROWED, Option::IS_CACHED_RUN, Option::SKIPPED_RECTOR_RULES, Option::SKIPPED_NON_RECTOR_CLASSES, Option::SKIPPED_START_WITH_SHORT_OPEN_TAG_FILES, Option::DEPRECATED_PHP_SETS_METHODS, Option::DEPRECATED_ATTRIBUTES_SETS_ARGS, Option::DEPRECATED_COMPOSER_BASED_ARGS, Option::LEVEL_OVERFLOWS, Option::CACHE_META_EXTENSIONS, Option::COMPOSER_BOUND_RULE_CONFIGURATIONS, Option::ROOT_STANDALONE_REGISTERED_RULES, Option::SET_REGISTERED_RULES];
    /**
     * Parameters compared by direction instead of the strict hash: adding a rule/set or removing a
     * skip means more work and must drop the cache, while removing a rule/set or adding a skip is
     * safe and keeps it. Handled in ChangedFilesDetector, so they are excluded from the strict hash.
     *
     * @var array<Option::*>
     */
    private const CACHE_DIRECTIONAL_PARAMETER_NAMES = [Option::REGISTERED_RECTOR_RULES, Option::REGISTERED_RECTOR_SETS, Option::SKIP];
    /**
     * @var array<string, mixed>
     */
    private static array $parameters = [];
    /**
     * @param Option::* $name
     * @param mixed $value
     */
    public static function addParameter(string $name, $value): void
    {
        if (is_array($value)) {
            $mergedParameters = array_merge(self::$parameters[$name] ?? [], $value);
            self::$parameters[$name] = $mergedParameters;
        } else {
            self::$parameters[$name][] = $value;
        }
    }
    /**
     * @param Option::* $name
     * @param mixed $value
     */
    public static function setParameter(string $name, $value): void
    {
        self::$parameters[$name] = $value;
    }
    /**
     * @param Option::* $name
     * @return mixed[]
     */
    public static function provideArrayParameter(string $name): array
    {
        $parameter = self::$parameters[$name] ?? [];
        Assert::isArray($parameter);
        $arrayIsListFunction = function (array $array): bool {
            if (function_exists('array_is_list')) {
                return array_is_list($array);
            }
            if ($array === []) {
                return \true;
            }
            $current_key = 0;
            foreach ($array as $key => $noop) {
                if ($key !== $current_key) {
                    return \false;
                }
                ++$current_key;
            }
            return \true;
        };
        if ($arrayIsListFunction($parameter)) {
            // remove duplicates
            $uniqueParameters = array_unique($parameter, \SORT_REGULAR);
            return array_values($uniqueParameters);
        }
        return $parameter;
    }
    /**
     * @param Option::* $name
     */
    public static function hasParameter(string $name): bool
    {
        return array_key_exists($name, self::$parameters);
    }
    /**
     * @param Option::* $name
     */
    public static function provideStringParameter(string $name, ?string $default = null): string
    {
        if ($default === null) {
            self::ensureParameterIsSet($name);
        }
        return self::$parameters[$name] ?? $default;
    }
    public static function provideIntParameter(string $key): int
    {
        return self::$parameters[$key];
    }
    /**
     * @param Option::* $name
     */
    public static function provideBoolParameter(string $name, ?bool $default = null): bool
    {
        if ($default === null) {
            self::ensureParameterIsSet($name);
        }
        return self::$parameters[$name] ?? $default;
    }
    /**
     * @api
     * Strict hash for cache invalidation. Ignored and directionally compared parameters are left
     * out, so only a real change to an output-affecting parameter drops the cache.
     */
    public static function hashForCacheInvalidation(): string
    {
        $strictParameters = self::$parameters;
        foreach (array_merge(self::CACHE_IGNORED_PARAMETER_NAMES, self::CACHE_DIRECTIONAL_PARAMETER_NAMES) as $ignoredName) {
            unset($strictParameters[$ignoredName]);
        }
        ksort($strictParameters);
        return sha1(serialize($strictParameters));
    }
    /**
     * @api
     * @return array{rules: mixed[], sets: mixed[], skip: mixed[]}
     */
    public static function provideCacheDirectionalParameters(): array
    {
        return ['rules' => self::$parameters[Option::REGISTERED_RECTOR_RULES] ?? [], 'sets' => self::$parameters[Option::REGISTERED_RECTOR_SETS] ?? [], 'skip' => self::$parameters[Option::SKIP] ?? []];
    }
    /**
     * @param Option::* $name
     */
    private static function ensureParameterIsSet(string $name): void
    {
        if (array_key_exists($name, self::$parameters)) {
            return;
        }
        throw new ShouldNotHappenException(sprintf('Parameter "%s" was not found', $name));
    }
}
