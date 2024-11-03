<?php

declare (strict_types=1);
namespace Rector\Configuration\Parameter;

use Rector\Configuration\Option;
use Rector\Exception\ShouldNotHappenException;
use RectorPrefix202411\Webmozart\Assert\Assert;
/**
 * @api
 */
final class SimpleParameterProvider
{
    /**
     * @var array<string, mixed>
     */
    private static $parameters = [];
    /**
     * @param Option::* $name
     * @param mixed $value
     */
    public static function addParameter(string $name, $value) : void
    {
        if (\is_array($value)) {
            $mergedParameters = \array_merge(self::$parameters[$name] ?? [], $value);
            self::$parameters[$name] = $mergedParameters;
        } else {
            self::$parameters[$name][] = $value;
        }
    }
    /**
     * @param Option::* $name
     * @param mixed $value
     */
    public static function setParameter(string $name, $value) : void
    {
        self::$parameters[$name] = $value;
    }
    /**
     * @param Option::* $name
     * @return mixed[]
     */
    public static function provideArrayParameter(string $name) : array
    {
        $parameter = self::$parameters[$name] ?? [];
        Assert::isArray($parameter);
        $arrayIsListFunction = function (array $array) : bool {
            if (\function_exists('array_is_list')) {
                return \array_is_list($array);
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
            $uniqueParameters = \array_unique($parameter);
            return \array_values($uniqueParameters);
        }
        return $parameter;
    }
    /**
     * @param Option::* $name
     */
    public static function hasParameter(string $name) : bool
    {
        return \array_key_exists($name, self::$parameters);
    }
    /**
     * @param Option::* $name
     */
    public static function provideStringParameter(string $name, ?string $default = null) : string
    {
        if ($default === null) {
            self::ensureParameterIsSet($name);
        }
        return self::$parameters[$name] ?? $default;
    }
    public static function provideIntParameter(string $key) : int
    {
        return self::$parameters[$key];
    }
    /**
     * @param Option::* $name
     */
    public static function provideBoolParameter(string $name, ?bool $default = null) : bool
    {
        if ($default === null) {
            self::ensureParameterIsSet($name);
        }
        return self::$parameters[$name] ?? $default;
    }
    /**
     * @api
     * For cache invalidation
     */
    public static function hash() : string
    {
        $parameterKeys = self::$parameters;
        return \sha1(\serialize($parameterKeys));
    }
    /**
     * @param Option::* $name
     */
    private static function ensureParameterIsSet(string $name) : void
    {
        if (\array_key_exists($name, self::$parameters)) {
            return;
        }
        throw new ShouldNotHappenException(\sprintf('Parameter "%s" was not found', $name));
    }
}
