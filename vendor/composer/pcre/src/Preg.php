<?php

/*
 * This file is part of composer/pcre.
 *
 * (c) Composer <https://github.com/composer>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */
namespace RectorPrefix20220531\Composer\Pcre;

class Preg
{
    /** @internal */
    public const ARRAY_MSG = '$subject as an array is not supported. You can use \'foreach\' instead.';
    /** @internal */
    public const INVALID_TYPE_MSG = '$subject must be a string, %s given.';
    /**
     * @param non-empty-string   $pattern
     * @param array<string|null> $matches Set by method
     * @param int      $flags PREG_UNMATCHED_AS_NULL is always set, no other flags are supported
     * @return 0|1
     */
    public static function match(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0) : int
    {
        if (($flags & \PREG_OFFSET_CAPTURE) !== 0) {
            throw new \InvalidArgumentException('PREG_OFFSET_CAPTURE is not supported as it changes the type of $matches, use matchWithOffsets() instead');
        }
        $result = \preg_match($pattern, $subject, $matches, $flags | \PREG_UNMATCHED_AS_NULL, $offset);
        if ($result === \false) {
            throw \RectorPrefix20220531\Composer\Pcre\PcreException::fromFunction('preg_match', $pattern);
        }
        return $result;
    }
    /**
     * Runs preg_match with PREG_OFFSET_CAPTURE
     *
     * @param non-empty-string   $pattern
     * @param array<int|string, array{string|null, int}> $matches Set by method
     * @param int      $flags PREG_UNMATCHED_AS_NULL and PREG_MATCH_OFFSET are always set, no other flags are supported
     * @return 0|1
     *
     * @phpstan-param array<int|string, array{string|null, int<-1, max>}> $matches
     */
    public static function matchWithOffsets(string $pattern, string $subject, ?array &$matches, int $flags = 0, int $offset = 0) : int
    {
        $result = \preg_match($pattern, $subject, $matches, $flags | \PREG_UNMATCHED_AS_NULL | \PREG_OFFSET_CAPTURE, $offset);
        if ($result === \false) {
            throw \RectorPrefix20220531\Composer\Pcre\PcreException::fromFunction('preg_match', $pattern);
        }
        return $result;
    }
    /**
     * @param non-empty-string   $pattern
     * @param array<int|string, list<string|null>> $matches Set by method
     * @param int      $flags PREG_UNMATCHED_AS_NULL is always set, no other flags are supported
     * @return 0|positive-int
     */
    public static function matchAll(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0) : int
    {
        if (($flags & \PREG_OFFSET_CAPTURE) !== 0) {
            throw new \InvalidArgumentException('PREG_OFFSET_CAPTURE is not supported as it changes the type of $matches, use matchAllWithOffsets() instead');
        }
        if (($flags & \PREG_SET_ORDER) !== 0) {
            throw new \InvalidArgumentException('PREG_SET_ORDER is not supported as it changes the type of $matches');
        }
        $result = \preg_match_all($pattern, $subject, $matches, $flags | \PREG_UNMATCHED_AS_NULL, $offset);
        if ($result === \false || $result === null) {
            throw \RectorPrefix20220531\Composer\Pcre\PcreException::fromFunction('preg_match_all', $pattern);
        }
        return $result;
    }
    /**
     * Runs preg_match_all with PREG_OFFSET_CAPTURE
     *
     * @param non-empty-string   $pattern
     * @param array<int|string, list<array{string|null, int}>> $matches Set by method
     * @param int      $flags PREG_UNMATCHED_AS_NULL and PREG_MATCH_OFFSET are always set, no other flags are supported
     * @return 0|positive-int
     *
     * @phpstan-param array<int|string, list<array{string|null, int<-1, max>}>> $matches
     */
    public static function matchAllWithOffsets(string $pattern, string $subject, ?array &$matches, int $flags = 0, int $offset = 0) : int
    {
        $result = \preg_match_all($pattern, $subject, $matches, $flags | \PREG_UNMATCHED_AS_NULL | \PREG_OFFSET_CAPTURE, $offset);
        if ($result === \false || $result === null) {
            throw \RectorPrefix20220531\Composer\Pcre\PcreException::fromFunction('preg_match_all', $pattern);
        }
        return $result;
    }
    /**
     * @param string|string[] $pattern
     * @param string|string[] $replacement
     * @param string $subject
     * @param int             $count Set by method
     */
    public static function replace($pattern, $replacement, $subject, int $limit = -1, int &$count = null) : string
    {
        if (!\is_scalar($subject)) {
            if (\is_array($subject)) {
                throw new \InvalidArgumentException(static::ARRAY_MSG);
            }
            throw new \TypeError(\sprintf(static::INVALID_TYPE_MSG, \gettype($subject)));
        }
        $result = \preg_replace($pattern, $replacement, $subject, $limit, $count);
        if ($result === null) {
            throw \RectorPrefix20220531\Composer\Pcre\PcreException::fromFunction('preg_replace', $pattern);
        }
        return $result;
    }
    /**
     * @param string|string[] $pattern
     * @param string $subject
     * @param int             $count Set by method
     * @param int             $flags PREG_OFFSET_CAPTURE is supported, PREG_UNMATCHED_AS_NULL is always set
     */
    public static function replaceCallback($pattern, callable $replacement, $subject, int $limit = -1, int &$count = null, int $flags = 0) : string
    {
        if (!\is_scalar($subject)) {
            if (\is_array($subject)) {
                throw new \InvalidArgumentException(static::ARRAY_MSG);
            }
            throw new \TypeError(\sprintf(static::INVALID_TYPE_MSG, \gettype($subject)));
        }
        $result = \preg_replace_callback($pattern, $replacement, $subject, $limit, $count, $flags | \PREG_UNMATCHED_AS_NULL);
        if ($result === null) {
            throw \RectorPrefix20220531\Composer\Pcre\PcreException::fromFunction('preg_replace_callback', $pattern);
        }
        return $result;
    }
    /**
     * @param array<string, callable> $pattern
     * @param string $subject
     * @param int    $count Set by method
     * @param int    $flags PREG_OFFSET_CAPTURE is supported, PREG_UNMATCHED_AS_NULL is always set
     */
    public static function replaceCallbackArray(array $pattern, $subject, int $limit = -1, int &$count = null, int $flags = 0) : string
    {
        if (!\is_scalar($subject)) {
            if (\is_array($subject)) {
                throw new \InvalidArgumentException(static::ARRAY_MSG);
            }
            throw new \TypeError(\sprintf(static::INVALID_TYPE_MSG, \gettype($subject)));
        }
        $result = \preg_replace_callback_array($pattern, $subject, $limit, $count, $flags | \PREG_UNMATCHED_AS_NULL);
        if ($result === null) {
            $pattern = \array_keys($pattern);
            throw \RectorPrefix20220531\Composer\Pcre\PcreException::fromFunction('preg_replace_callback_array', $pattern);
        }
        return $result;
    }
    /**
     * @param int    $flags PREG_SPLIT_NO_EMPTY or PREG_SPLIT_DELIM_CAPTURE
     * @return list<string>
     */
    public static function split(string $pattern, string $subject, int $limit = -1, int $flags = 0) : array
    {
        if (($flags & \PREG_SPLIT_OFFSET_CAPTURE) !== 0) {
            throw new \InvalidArgumentException('PREG_SPLIT_OFFSET_CAPTURE is not supported as it changes the type of $matches, use splitWithOffsets() instead');
        }
        $result = \preg_split($pattern, $subject, $limit, $flags);
        if ($result === \false) {
            throw \RectorPrefix20220531\Composer\Pcre\PcreException::fromFunction('preg_split', $pattern);
        }
        return $result;
    }
    /**
     * @param int    $flags PREG_SPLIT_NO_EMPTY or PREG_SPLIT_DELIM_CAPTURE, PREG_SPLIT_OFFSET_CAPTURE is always set
     * @return list<array{string, int}>
     * @phpstan-return list<array{string, int<0, max>}>
     */
    public static function splitWithOffsets(string $pattern, string $subject, int $limit = -1, int $flags = 0) : array
    {
        $result = \preg_split($pattern, $subject, $limit, $flags | \PREG_SPLIT_OFFSET_CAPTURE);
        if ($result === \false) {
            throw \RectorPrefix20220531\Composer\Pcre\PcreException::fromFunction('preg_split', $pattern);
        }
        return $result;
    }
    /**
     * @template T of string|\Stringable
     * @param string   $pattern
     * @param array<T> $array
     * @param int      $flags PREG_GREP_INVERT
     * @return array<T>
     */
    public static function grep(string $pattern, array $array, int $flags = 0) : array
    {
        $result = \preg_grep($pattern, $array, $flags);
        if ($result === \false) {
            throw \RectorPrefix20220531\Composer\Pcre\PcreException::fromFunction('preg_grep', $pattern);
        }
        return $result;
    }
    /**
     * @param non-empty-string   $pattern
     * @param array<string|null> $matches Set by method
     * @param int      $flags PREG_UNMATCHED_AS_NULL is always set, no other flags are supported
     */
    public static function isMatch(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0) : bool
    {
        return (bool) static::match($pattern, $subject, $matches, $flags, $offset);
    }
    /**
     * @param non-empty-string   $pattern
     * @param array<int|string, list<string|null>> $matches Set by method
     * @param int      $flags PREG_UNMATCHED_AS_NULL is always set, no other flags are supported
     */
    public static function isMatchAll(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, int $offset = 0) : bool
    {
        return (bool) static::matchAll($pattern, $subject, $matches, $flags, $offset);
    }
    /**
     * Runs preg_match with PREG_OFFSET_CAPTURE
     *
     * @param non-empty-string   $pattern
     * @param array<int|string, array{string|null, int}> $matches Set by method
     * @param int      $flags PREG_UNMATCHED_AS_NULL is always set, no other flags are supported
     *
     * @phpstan-param array<int|string, array{string|null, int<-1, max>}> $matches
     */
    public static function isMatchWithOffsets(string $pattern, string $subject, ?array &$matches, int $flags = 0, int $offset = 0) : bool
    {
        return (bool) static::matchWithOffsets($pattern, $subject, $matches, $flags, $offset);
    }
    /**
     * Runs preg_match_all with PREG_OFFSET_CAPTURE
     *
     * @param non-empty-string   $pattern
     * @param array<int|string, list<array{string|null, int}>> $matches Set by method
     * @param int      $flags PREG_UNMATCHED_AS_NULL is always set, no other flags are supported
     *
     * @phpstan-param array<int|string, list<array{string|null, int<-1, max>}>> $matches
     */
    public static function isMatchAllWithOffsets(string $pattern, string $subject, ?array &$matches, int $flags = 0, int $offset = 0) : bool
    {
        return (bool) static::matchAllWithOffsets($pattern, $subject, $matches, $flags, $offset);
    }
}
