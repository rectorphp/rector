<?php

declare (strict_types=1);
namespace Rector\FileSystem;

/**
 * Keeps only files matching all given --filter patterns.
 *
 * @see \Rector\Tests\FileSystem\FilePathFilter\FilePathFilterTest
 */
final class FilePathFilter
{
    /**
     * @var string
     */
    private const TESTS_KEYWORD = 'tests';
    /**
     * Splits a comma-separated --filter value into individual patterns, trimming blanks.
     *
     * @return string[]
     */
    public function parsePatterns(string $rawFilter): array
    {
        $patterns = [];
        foreach (explode(',', $rawFilter) as $pattern) {
            $pattern = trim($pattern);
            if ($pattern !== '') {
                $patterns[] = $pattern;
            }
        }
        return $patterns;
    }
    /**
     * Keeps only files that match every pattern (AND). With no patterns the input is returned unchanged.
     *
     * @param string[] $filePaths
     * @param string[] $patterns
     * @return string[]
     */
    public function filter(array $filePaths, array $patterns): array
    {
        if ($patterns === []) {
            return $filePaths;
        }
        return array_values(array_filter($filePaths, fn(string $filePath): bool => $this->matchesAllPatterns($filePath, $patterns)));
    }
    /**
     * @param string[] $patterns
     */
    private function matchesAllPatterns(string $filePath, array $patterns): bool
    {
        $found = \true;
        foreach ($patterns as $pattern) {
            if (!$this->matchesPattern($filePath, $pattern)) {
                $found = \false;
                break;
            }
        }
        return $found;
    }
    /**
     * Three kinds of pattern are recognised:
     *  - "tests"           the basename ends in Test.php or TestCase.php
     *  - contains "*"      glob matched against the full path when it has a "/", else against the basename
     *  - anything else     substring matched anywhere in the full path, e.g. /Controller/
     */
    private function matchesPattern(string $filePath, string $pattern): bool
    {
        if ($pattern === self::TESTS_KEYWORD) {
            $basename = basename($filePath);
            return substr_compare($basename, 'Test.php', -strlen('Test.php')) === 0 || substr_compare($basename, 'TestCase.php', -strlen('TestCase.php')) === 0;
        }
        if (strpos($pattern, '*') !== \false) {
            $subject = strpos($pattern, '/') !== \false ? $filePath : basename($filePath);
            return fnmatch($pattern, $subject);
        }
        return strpos($filePath, $pattern) !== \false;
    }
}
