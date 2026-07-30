<?php

declare (strict_types=1);
namespace Rector\Skipper\Matcher;

use Rector\Skipper\FileSystem\PathNormalizer;
/**
 * @see \Rector\Tests\Skipper\Matcher\FileInfoMatcherTest
 */
final class FileInfoMatcher
{
    /**
     * Returns the original (un-normalized) pattern that matched, so callers can report the exact
     * configured path. Returns null when no pattern matches.
     *
     * @param string[] $filePatterns
     */
    public function matchPattern(string $filePath, array $filePatterns): ?string
    {
        $normalizedFilePath = PathNormalizer::normalize($filePath);
        foreach ($filePatterns as $filePattern) {
            $normalizedFilePattern = PathNormalizer::normalize($filePattern);
            if ($this->doesFileMatchPattern($normalizedFilePath, $normalizedFilePattern)) {
                return $filePattern;
            }
        }
        return null;
    }
    /**
     * Supports both relative and absolute $file path. They differ for PHP-CS-Fixer and PHP_CodeSniffer.
     */
    private function doesFileMatchPattern(string $filePath, string $ignoredPath): bool
    {
        // in rector.php, the path can be absolute
        if ($filePath === $ignoredPath) {
            return \true;
        }
        $ignoredPath = $this->normalizeForFnmatch($ignoredPath);
        if ($ignoredPath === '') {
            return \false;
        }
        if (strncmp($filePath, $ignoredPath, strlen($ignoredPath)) === 0) {
            return \true;
        }
        if (substr_compare($filePath, $ignoredPath, -strlen($ignoredPath)) === 0) {
            return \true;
        }
        if ($this->matchFnmatch($ignoredPath, $filePath)) {
            return \true;
        }
        return $this->matchRealpath($ignoredPath, $filePath);
    }
    private function normalizeForFnmatch(string $path): string
    {
        if (substr_compare($path, '*', -strlen('*')) === 0 || strncmp($path, '*', strlen('*')) === 0) {
            return '*' . trim($path, '*') . '*';
        }
        if (strpos($path, '..') !== \false) {
            $realPath = realpath($path);
            if ($realPath === \false) {
                return '';
            }
            return PathNormalizer::normalize($realPath);
        }
        return $path;
    }
    private function matchFnmatch(string $matchingPath, string $filePath): bool
    {
        if (fnmatch($matchingPath, $filePath)) {
            return \true;
        }
        // in case of relative compare
        return fnmatch('*/' . $matchingPath, $filePath);
    }
    private function matchRealpath(string $matchingPath, string $filePath): bool
    {
        $realPathMatchingPath = realpath($matchingPath);
        if ($realPathMatchingPath === \false) {
            return \false;
        }
        $realpathFilePath = realpath($filePath);
        if ($realpathFilePath === \false) {
            return \false;
        }
        $normalizedMatchingPath = PathNormalizer::normalize($realPathMatchingPath);
        $normalizedFilePath = PathNormalizer::normalize($realpathFilePath);
        // skip define direct path exactly equal
        if ($normalizedMatchingPath === $normalizedFilePath) {
            return \true;
        }
        // ensure add / suffix to ensure no same prefix directory
        $suffixedMatchingPath = rtrim($normalizedMatchingPath, '/') . '/';
        return strncmp($normalizedFilePath, $suffixedMatchingPath, strlen($suffixedMatchingPath)) === 0;
    }
}
