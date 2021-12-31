<?php

/*
 * This file is part of composer/pcre.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
namespace RectorPrefix20211231\Composer\Pcre;

class Preg
{
    const ARRAY_MSG = '$subject as an array is not supported. You can use \'foreach\' instead.';
    /**
     * @param string   $pattern
     * @param string   $subject
     * @param array<string|null> $matches Set by method
     * @param int      $flags PREG_UNMATCHED_AS_NULL, only available on PHP 7.2+
     * @param int      $offset
     * @return int
     */
    public static function match($pattern, $subject, &$matches = null, $flags = 0, $offset = 0)
    {
        if (($flags & \PREG_OFFSET_CAPTURE) !== 0) {
            throw new \InvalidArgumentException('PREG_OFFSET_CAPTURE is not supported as it changes the type of $matches, use matchWithOffsets() instead');
        }
        $result = \preg_match($pattern, $subject, $matches, $flags, $offset);
        if ($result === \false) {
            throw \RectorPrefix20211231\Composer\Pcre\PcreException::fromFunction('preg_match', $pattern);
        }
        return $result;
    }
    /**
     * Runs preg_match_all with PREG_OFFSET_CAPTURE
     *
     * @param string   $pattern
     * @param string   $subject
     * @param array<int|string, array{string|null, int}> $matches Set by method
     * @param int      $flags PREG_UNMATCHED_AS_NULL, only available on PHP 7.2+
     * @param int      $offset
     * @return int
     *
     * @phpstan-param array<int|string, array{string|null, int<-1, max>}> $matches
     */
    public static function matchWithOffsets($pattern, $subject, &$matches, $flags = 0, $offset = 0)
    {
        $result = \preg_match($pattern, $subject, $matches, $flags | \PREG_OFFSET_CAPTURE, $offset);
        if ($result === \false) {
            throw \RectorPrefix20211231\Composer\Pcre\PcreException::fromFunction('preg_match', $pattern);
        }
        return $result;
    }
    /**
     * @param string   $pattern
     * @param string   $subject
     * @param array<int|string, list<string|null>> $matches Set by method
     * @param int      $flags PREG_UNMATCHED_AS_NULL, only available on PHP 7.2+
     * @param int      $offset
     * @return int
     */
    public static function matchAll($pattern, $subject, &$matches = null, $flags = 0, $offset = 0)
    {
        if (($flags & \PREG_OFFSET_CAPTURE) !== 0) {
            throw new \InvalidArgumentException('PREG_OFFSET_CAPTURE is not supported as it changes the type of $matches, use matchAllWithOffsets() instead');
        }
        if (($flags & \PREG_SET_ORDER) !== 0) {
            throw new \InvalidArgumentException('PREG_SET_ORDER is not supported as it changes the type of $matches');
        }
        $result = \preg_match_all($pattern, $subject, $matches, $flags, $offset);
        if ($result === \false || $result === null) {
            throw \RectorPrefix20211231\Composer\Pcre\PcreException::fromFunction('preg_match_all', $pattern);
        }
        return $result;
    }
    /**
     * Runs preg_match_all with PREG_OFFSET_CAPTURE
     *
     * @param string   $pattern
     * @param string   $subject
     * @param array<int|string, list<array{string|null, int}>> $matches Set by method
     * @param int      $flags PREG_UNMATCHED_AS_NULL, only available on PHP 7.2+
     * @param int      $offset
     * @return int
     *
     * @phpstan-param array<int|string, list<array{string|null, int<-1, max>}>> $matches
     */
    public static function matchAllWithOffsets($pattern, $subject, &$matches, $flags = 0, $offset = 0)
    {
        $result = \preg_match_all($pattern, $subject, $matches, $flags | \PREG_OFFSET_CAPTURE, $offset);
        if ($result === \false || $result === null) {
            throw \RectorPrefix20211231\Composer\Pcre\PcreException::fromFunction('preg_match_all', $pattern);
        }
        return $result;
    }
    /**
     * @param string|string[] $pattern
     * @param string|string[] $replacement
     * @param string          $subject
     * @param int             $limit
     * @param int             $count Set by method
     * @return string
     */
    public static function replace($pattern, $replacement, $subject, $limit = -1, &$count = null)
    {
        if (\is_array($subject)) {
            // @phpstan-ignore-line
            throw new \InvalidArgumentException(static::ARRAY_MSG);
        }
        $result = \preg_replace($pattern, $replacement, $subject, $limit, $count);
        if ($result === null) {
            throw \RectorPrefix20211231\Composer\Pcre\PcreException::fromFunction('preg_replace', $pattern);
        }
        return $result;
    }
    /**
     * @param string|string[] $pattern
     * @param callable        $replacement
     * @param string          $subject
     * @param int             $limit
     * @param int             $count Set by method
     * @param int             $flags PREG_OFFSET_CAPTURE or PREG_UNMATCHED_AS_NULL, only available on PHP 7.4+
     * @return string
     */
    public static function replaceCallback($pattern, $replacement, $subject, $limit = -1, &$count = null, $flags = 0)
    {
        if (\is_array($subject)) {
            // @phpstan-ignore-line
            throw new \InvalidArgumentException(static::ARRAY_MSG);
        }
        if (\PHP_VERSION_ID >= 70400) {
            $result = \preg_replace_callback($pattern, $replacement, $subject, $limit, $count, $flags);
        } else {
            $result = \preg_replace_callback($pattern, $replacement, $subject, $limit, $count);
        }
        if ($result === null) {
            throw \RectorPrefix20211231\Composer\Pcre\PcreException::fromFunction('preg_replace_callback', $pattern);
        }
        return $result;
    }
    /**
     * Available from PHP 7.0
     *
     * @param array<string, callable> $pattern
     * @param string $subject
     * @param int    $limit
     * @param int    $count Set by method
     * @param int    $flags PREG_OFFSET_CAPTURE or PREG_UNMATCHED_AS_NULL, only available on PHP 7.4+
     * @return string
     */
    public static function replaceCallbackArray(array $pattern, $subject, $limit = -1, &$count = null, $flags = 0)
    {
        if (\is_array($subject)) {
            // @phpstan-ignore-line
            throw new \InvalidArgumentException(static::ARRAY_MSG);
        }
        if (\PHP_VERSION_ID >= 70400) {
            $result = \preg_replace_callback_array($pattern, $subject, $limit, $count, $flags);
        } else {
            $result = \preg_replace_callback_array($pattern, $subject, $limit, $count);
        }
        if ($result === null) {
            $pattern = \array_keys($pattern);
            throw \RectorPrefix20211231\Composer\Pcre\PcreException::fromFunction('preg_replace_callback_array', $pattern);
        }
        return $result;
    }
    /**
     * @param string $pattern
     * @param string $subject
     * @param int    $limit
     * @param int    $flags PREG_SPLIT_NO_EMPTY or PREG_SPLIT_DELIM_CAPTURE
     * @return list<string>
     */
    public static function split($pattern, $subject, $limit = -1, $flags = 0)
    {
        if (($flags & \PREG_SPLIT_OFFSET_CAPTURE) !== 0) {
            throw new \InvalidArgumentException('PREG_SPLIT_OFFSET_CAPTURE is not supported as it changes the type of $matches, use splitWithOffsets() instead');
        }
        $result = \preg_split($pattern, $subject, $limit, $flags);
        if ($result === \false) {
            throw \RectorPrefix20211231\Composer\Pcre\PcreException::fromFunction('preg_split', $pattern);
        }
        return $result;
    }
    /**
     * @param string $pattern
     * @param string $subject
     * @param int    $limit
     * @param int    $flags PREG_SPLIT_NO_EMPTY or PREG_SPLIT_DELIM_CAPTURE
     * @return list<array{string, int}>
     * @phpstan-return list<array{string, int<0, max>}>
     */
    public static function splitWithOffsets($pattern, $subject, $limit = -1, $flags = 0)
    {
        $result = \preg_split($pattern, $subject, $limit, $flags | \PREG_SPLIT_OFFSET_CAPTURE);
        if ($result === \false) {
            throw \RectorPrefix20211231\Composer\Pcre\PcreException::fromFunction('preg_split', $pattern);
        }
        // @phpstan-ignore-next-line See https://github.com/phpstan/phpstan/issues/6155
        return $result;
    }
    /**
     * @template T of string|\Stringable
     * @param string   $pattern
     * @param array<T> $array
     * @param int      $flags PREG_GREP_INVERT
     * @return array<T>
     */
    public static function grep($pattern, array $array, $flags = 0)
    {
        $result = \preg_grep($pattern, $array, $flags);
        if ($result === \false) {
            throw \RectorPrefix20211231\Composer\Pcre\PcreException::fromFunction('preg_grep', $pattern);
        }
        return $result;
    }
    /**
     * @param string   $pattern
     * @param string   $subject
     * @param array<string|null> $matches Set by method
     * @param int      $flags PREG_UNMATCHED_AS_NULL, only available on PHP 7.2+
     * @param int      $offset
     * @return bool
     */
    public static function isMatch($pattern, $subject, &$matches = null, $flags = 0, $offset = 0)
    {
        return (bool) static::match($pattern, $subject, $matches, $flags, $offset);
    }
    /**
     * @param string   $pattern
     * @param string   $subject
     * @param array<int|string, list<string|null>> $matches Set by method
     * @param int      $flags PREG_UNMATCHED_AS_NULL, only available on PHP 7.2+
     * @param int      $offset
     * @return bool
     */
    public static function isMatchAll($pattern, $subject, &$matches = null, $flags = 0, $offset = 0)
    {
        return (bool) static::matchAll($pattern, $subject, $matches, $flags, $offset);
    }
    /**
     * Runs preg_match_all with PREG_OFFSET_CAPTURE
     *
     * @param string   $pattern
     * @param string   $subject
     * @param array<int|string, array{string|null, int}> $matches Set by method
     * @param int      $flags PREG_UNMATCHED_AS_NULL, only available on PHP 7.2+
     * @param int      $offset
     * @return bool
     *
     * @phpstan-param array<int|string, array{string|null, int<-1, max>}> $matches
     */
    public static function isMatchWithOffsets($pattern, $subject, &$matches, $flags = 0, $offset = 0)
    {
        return (bool) static::matchWithOffsets($pattern, $subject, $matches, $flags, $offset);
    }
    /**
     * Runs preg_match_all with PREG_OFFSET_CAPTURE
     *
     * @param string   $pattern
     * @param string   $subject
     * @param array<int|string, list<array{string|null, int}>> $matches Set by method
     * @param int      $flags PREG_UNMATCHED_AS_NULL, only available on PHP 7.2+
     * @param int      $offset
     * @return bool
     *
     * @phpstan-param array<int|string, list<array{string|null, int<-1, max>}>> $matches
     */
    public static function isMatchAllWithOffsets($pattern, $subject, &$matches, $flags = 0, $offset = 0)
    {
        return (bool) static::matchAllWithOffsets($pattern, $subject, $matches, $flags, $offset);
    }
}
