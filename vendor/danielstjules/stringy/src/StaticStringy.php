<?php

namespace RectorPrefix20211020\Stringy;

use BadMethodCallException;
use ReflectionClass;
use ReflectionMethod;
/**
 * Class StaticStringy
 *
 * @method static string append(string $str, string $stringAppend, string $encoding = null)
 * @method static string at(string $str, int $index, string $encoding = null)
 * @method static string between(string $str, string $start, string $end, int $offset = 0, string $encoding = null)
 * @method static string camelize(string $str, string $encoding = null)
 * @method static string chars(string $str, string $encoding = null)
 * @method static string collapseWhitespace(string $str, string $encoding = null)
 * @method static bool contains(string $str, string $needle, bool $caseSensitive = true, string $encoding = null)
 * @method static bool containsAll(string $str, string[] $needle, bool $caseSensitive = true, string $encoding = null)
 * @method static bool containsAny(string $str, string[] $needle, bool $caseSensitive = true, string $encoding = null)
 * @method static int count(string $str, string $encoding = null)
 * @method static int countSubstr(string $str, string $substring, bool $caseSensitive = true, string $encoding = null)
 * @method static string dasherize(string $str, string $encoding = null)
 * @method static string delimit(string $str, string $delimiter, string $encoding = null)
 * @method static bool endsWith(string $str, string $substring, bool $caseSensitive = true, string $encoding = null)
 * @method static bool endsWithAny(string $str, string[] $substrings, bool $caseSensitive = true, string $encoding = null)
 * @method static string ensureLeft(string $str, string $substring, string $encoding = null)
 * @method static string ensureRight(string $str, string $substring, string $encoding = null)
 * @method static string first(string $str, int $n, string $encoding = null)
 * @method static bool hasLowerCase(string $str, string $encoding = null)
 * @method static bool hasUpperCase(string $str, string $encoding = null)
 * @method static string htmlDecode(string $str, int $flags = ENT_COMPAT, string $encoding = null)
 * @method static string htmlEncode(string $str, int $flags = ENT_COMPAT, string $encoding = null)
 * @method static string humanize(string $str, string $encoding = null)
 * @method static int indexOf(string $str, string $needle, int $offset = 0, string $encoding = null)
 * @method static int indexOfLast(string $str, string $needle, int $offset = 0, string $encoding = null)
 * @method static string insert(string $str, string $substring, int $index = 0, string $encoding = null)
 * @method static bool isAlpha(string $str, string $encoding = null)
 * @method static bool isAlphanumeric(string $str, string $encoding = null)
 * @method static bool isBase64(string $str, string $encoding = null)
 * @method static bool isBlank(string $str, string $encoding = null)
 * @method static bool isHexadecimal(string $str, string $encoding = null)
 * @method static bool isJson(string $str, string $encoding = null)
 * @method static bool isLowerCase(string $str, string $encoding = null)
 * @method static bool isSerialized(string $str, string $encoding = null)
 * @method static bool isUpperCase(string $str, string $encoding = null)
 * @method static string last(string $str, string $encoding = null)
 * @method static int length(string $str, string $encoding = null)
 * @method static string[] lines(string $str, string $encoding = null)
 * @method static string longestCommonPrefix(string $str, string $otherStr, string $encoding = null)
 * @method static string longestCommonSuffix(string $str, string $otherStr, string $encoding = null)
 * @method static string longestCommonSubstring(string $str, string $otherStr, string $encoding = null)
 * @method static string lowerCaseFirst(string $str, string $encoding = null)
 * @method static string pad(string $str, int $length, string $padStr = ' ', string $padType = 'right', string $encoding = null)
 * @method static string padBoth(string $str, int $length, string $padStr = ' ', string $encoding = null)
 * @method static string padLeft(string $str, int $length, string $padStr = ' ', string $encoding = null)
 * @method static string padRight(string $str, int $length, string $padStr = ' ', string $encoding = null)
 * @method static string prepend(string $str, string $string, string $encoding = null)
 * @method static string regexReplace(string $str, string $pattern, string $replacement, string $options = 'msr', string $encoding = null)
 * @method static string removeLeft(string $str, string $substring, string $encoding = null)
 * @method static string removeRight(string $str, string $substring, string $encoding = null)
 * @method static string repeat(string $str, int $multiplier, string $encoding = null)
 * @method static string replace(string $str, string $search, string $replacement, string $encoding = null)
 * @method static string reverse(string $str, string $encoding = null)
 * @method static string safeTruncate(string $str, int $length, string $substring = '', string $encoding = null)
 * @method static string shuffle(string $str, string $encoding = null)
 * @method static string slugify(string $str, string $replacement = '-', string $encoding = null)
 * @method static string slice(string $str, int $start, int $end = null, string $encoding = null)
 * @method static string split(string $str, string $pattern, int $limit = null, string $encoding = null)
 * @method static bool startsWith(string $str, string $substring, bool $caseSensitive = true, string $encoding = null)
 * @method static bool startsWithAny(string $str, string[] $substrings, bool $caseSensitive = true, string $encoding = null)
 * @method static string stripWhitespace(string $str, string $encoding = null)
 * @method static string substr(string $str, int $start, int $length = null, string $encoding = null)
 * @method static string surround(string $str, string $substring, string $encoding = null)
 * @method static string swapCase(string $str, string $encoding = null)
 * @method static string tidy(string $str, string $encoding = null)
 * @method static string titleize(string $str, string $encoding = null)
 * @method static string toAscii(string $str, string $language = 'en', bool $removeUnsupported = true, string $encoding = null)
 * @method static bool toBoolean(string $str, string $encoding = null)
 * @method static string toLowerCase(string $str, string $encoding = null)
 * @method static string toSpaces(string $str, int $tabLength = 4, string $encoding = null)
 * @method static string toTabs(string $str, int $tabLength = 4, string $encoding = null)
 * @method static string toTitleCase(string $str, string $encoding = null)
 * @method static string toUpperCase(string $str, string $encoding = null)
 * @method static string trim(string $str, string $chars = null, string $encoding = null)
 * @method static string trimLeft(string $str, string $chars = null, string $encoding = null)
 * @method static string trimRight(string $str, string $chars = null, string $encoding = null)
 * @method static string truncate(string $str, int $length, string $substring = '', string $encoding = null)
 * @method static string underscored(string $str, string $encoding = null)
 * @method static string upperCamelize(string $str, string $encoding = null)
 * @method static string upperCaseFirst(string $str, string $encoding = null)
 */
class StaticStringy
{
    /**
     * A mapping of method names to the numbers of arguments it accepts. Each
     * should be two more than the equivalent Stringy method. Necessary as
     * static methods place the optional $encoding as the last parameter.
     *
     * @var string[]
     */
    protected static $methodArgs = null;
    /**
     * Creates an instance of Stringy and invokes the given method with the
     * rest of the passed arguments. The optional encoding is expected to be
     * the last argument. For example, the following:
     * StaticStringy::slice('fòôbàř', 0, 3, 'UTF-8'); translates to
     * Stringy::create('fòôbàř', 'UTF-8')->slice(0, 3);
     * The result is not cast, so the return value may be of type Stringy,
     * integer, boolean, etc.
     *
     * @param string  $name
     * @param mixed[] $arguments
     *
     * @return Stringy
     *
     * @throws \BadMethodCallException
     */
    public static function __callStatic($name, $arguments)
    {
        if (!static::$methodArgs) {
            $stringyClass = new \ReflectionClass('RectorPrefix20211020\\Stringy\\Stringy');
            $methods = $stringyClass->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                $params = $method->getNumberOfParameters() + 2;
                static::$methodArgs[$method->name] = $params;
            }
        }
        if (!isset(static::$methodArgs[$name])) {
            throw new \BadMethodCallException($name . ' is not a valid method');
        }
        $numArgs = \count($arguments);
        $str = $numArgs ? $arguments[0] : '';
        if ($numArgs === static::$methodArgs[$name]) {
            $args = \array_slice($arguments, 1, -1);
            $encoding = $arguments[$numArgs - 1];
        } else {
            $args = \array_slice($arguments, 1);
            $encoding = null;
        }
        $stringy = \RectorPrefix20211020\Stringy\Stringy::create($str, $encoding);
        $result = \call_user_func_array([$stringy, $name], $args);
        $cast = function ($val) {
            if (\is_object($val) && $val instanceof \RectorPrefix20211020\Stringy\Stringy) {
                return (string) $val;
            } else {
                return $val;
            }
        };
        return \is_array($result) ? \array_map($cast, $result) : $cast($result);
    }
}
