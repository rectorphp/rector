<?php

declare (strict_types=1);
namespace Rector\Core\Configuration\Parameter;

use RectorPrefix202307\Webmozart\Assert\Assert;
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
     * @param mixed $value
     */
    public static function addParameter(string $key, $value) : void
    {
        if (\is_array($value)) {
            $mergedParameters = \array_merge(self::$parameters[$key] ?? [], $value);
            self::$parameters[$key] = $mergedParameters;
        } else {
            self::$parameters[$key][] = $value;
        }
    }
    /**
     * @param mixed $value
     */
    public static function setParameter(string $key, $value) : void
    {
        self::$parameters[$key] = $value;
    }
    /**
     * @return mixed[]
     */
    public static function provideArrayParameter(string $key) : array
    {
        $parameter = self::$parameters[$key] ?? [];
        Assert::isArray($parameter);
        $arrayIsList = function (array $array) : bool {
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
        if ($arrayIsList($parameter)) {
            // remove duplicates
            $uniqueParameters = \array_unique($parameter);
            return \array_values($uniqueParameters);
        }
        return $parameter;
    }
    public static function hasParameter(string $name) : bool
    {
        return \array_key_exists($name, self::$parameters);
    }
    public static function provideStringParameter(string $key) : string
    {
        return self::$parameters[$key];
    }
    public static function provideIntParameter(string $key) : int
    {
        return self::$parameters[$key];
    }
    public static function provideBoolParameter(string $key) : bool
    {
        return self::$parameters[$key];
    }
}
