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

class Regex
{
    /**
     * @param non-empty-string $pattern
     */
    public static function isMatch(string $pattern, string $subject, int $offset = 0) : bool
    {
        return (bool) \RectorPrefix20220531\Composer\Pcre\Preg::match($pattern, $subject, $matches, 0, $offset);
    }
    /**
     * @param non-empty-string $pattern
     * @param int    $flags PREG_UNMATCHED_AS_NULL is always set, no other flags are supported
     */
    public static function match(string $pattern, string $subject, int $flags = 0, int $offset = 0) : \RectorPrefix20220531\Composer\Pcre\MatchResult
    {
        if (($flags & \PREG_OFFSET_CAPTURE) !== 0) {
            throw new \InvalidArgumentException('PREG_OFFSET_CAPTURE is not supported as it changes the return type, use matchWithOffsets() instead');
        }
        $count = \RectorPrefix20220531\Composer\Pcre\Preg::match($pattern, $subject, $matches, $flags, $offset);
        return new \RectorPrefix20220531\Composer\Pcre\MatchResult($count, $matches);
    }
    /**
     * Runs preg_match with PREG_OFFSET_CAPTURE
     *
     * @param non-empty-string $pattern
     * @param int    $flags PREG_UNMATCHED_AS_NULL and PREG_MATCH_OFFSET are always set, no other flags are supported
     */
    public static function matchWithOffsets(string $pattern, string $subject, int $flags = 0, int $offset = 0) : \RectorPrefix20220531\Composer\Pcre\MatchWithOffsetsResult
    {
        $count = \RectorPrefix20220531\Composer\Pcre\Preg::matchWithOffsets($pattern, $subject, $matches, $flags, $offset);
        return new \RectorPrefix20220531\Composer\Pcre\MatchWithOffsetsResult($count, $matches);
    }
    /**
     * @param non-empty-string $pattern
     * @param int    $flags PREG_UNMATCHED_AS_NULL is always set, no other flags are supported
     */
    public static function matchAll(string $pattern, string $subject, int $flags = 0, int $offset = 0) : \RectorPrefix20220531\Composer\Pcre\MatchAllResult
    {
        if (($flags & \PREG_OFFSET_CAPTURE) !== 0) {
            throw new \InvalidArgumentException('PREG_OFFSET_CAPTURE is not supported as it changes the return type, use matchAllWithOffsets() instead');
        }
        if (($flags & \PREG_SET_ORDER) !== 0) {
            throw new \InvalidArgumentException('PREG_SET_ORDER is not supported as it changes the return type');
        }
        $count = \RectorPrefix20220531\Composer\Pcre\Preg::matchAll($pattern, $subject, $matches, $flags, $offset);
        return new \RectorPrefix20220531\Composer\Pcre\MatchAllResult($count, $matches);
    }
    /**
     * Runs preg_match_all with PREG_OFFSET_CAPTURE
     *
     * @param non-empty-string $pattern
     * @param int    $flags PREG_UNMATCHED_AS_NULL and PREG_MATCH_OFFSET are always set, no other flags are supported
     */
    public static function matchAllWithOffsets(string $pattern, string $subject, int $flags = 0, int $offset = 0) : \RectorPrefix20220531\Composer\Pcre\MatchAllWithOffsetsResult
    {
        $count = \RectorPrefix20220531\Composer\Pcre\Preg::matchAllWithOffsets($pattern, $subject, $matches, $flags, $offset);
        return new \RectorPrefix20220531\Composer\Pcre\MatchAllWithOffsetsResult($count, $matches);
    }
    /**
     * @param string|string[] $pattern
     * @param string|string[] $replacement
     * @param string          $subject
     */
    public static function replace($pattern, $replacement, $subject, int $limit = -1) : \RectorPrefix20220531\Composer\Pcre\ReplaceResult
    {
        $result = \RectorPrefix20220531\Composer\Pcre\Preg::replace($pattern, $replacement, $subject, $limit, $count);
        return new \RectorPrefix20220531\Composer\Pcre\ReplaceResult($count, $result);
    }
    /**
     * @param string|string[] $pattern
     * @param string          $subject
     * @param int             $flags PREG_OFFSET_CAPTURE is supported, PREG_UNMATCHED_AS_NULL is always set
     */
    public static function replaceCallback($pattern, callable $replacement, $subject, int $limit = -1, int $flags = 0) : \RectorPrefix20220531\Composer\Pcre\ReplaceResult
    {
        $result = \RectorPrefix20220531\Composer\Pcre\Preg::replaceCallback($pattern, $replacement, $subject, $limit, $count, $flags);
        return new \RectorPrefix20220531\Composer\Pcre\ReplaceResult($count, $result);
    }
    /**
     * @param array<string, callable> $pattern
     * @param string $subject
     * @param int    $flags PREG_OFFSET_CAPTURE is supported, PREG_UNMATCHED_AS_NULL is always set
     */
    public static function replaceCallbackArray(array $pattern, $subject, int $limit = -1, int $flags = 0) : \RectorPrefix20220531\Composer\Pcre\ReplaceResult
    {
        $result = \RectorPrefix20220531\Composer\Pcre\Preg::replaceCallbackArray($pattern, $subject, $limit, $count, $flags);
        return new \RectorPrefix20220531\Composer\Pcre\ReplaceResult($count, $result);
    }
}
