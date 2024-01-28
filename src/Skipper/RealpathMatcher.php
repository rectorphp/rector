<?php

declare (strict_types=1);
namespace Rector\Skipper;

use Rector\Skipper\FileSystem\PathNormalizer;
final class RealpathMatcher
{
    public function match(string $matchingPath, string $filePath) : bool
    {
        /** @var string|false $realPathMatchingPath */
        $realPathMatchingPath = \realpath($matchingPath);
        if ($realPathMatchingPath === \false) {
            return \false;
        }
        /** @var string|false $realpathFilePath */
        $realpathFilePath = \realpath($filePath);
        if ($realpathFilePath === \false) {
            return \false;
        }
        $normalizedMatchingPath = PathNormalizer::normalize($realPathMatchingPath);
        $normalizedFilePath = PathNormalizer::normalize($realpathFilePath);
        // skip define direct path
        if (\is_file($normalizedMatchingPath)) {
            return $normalizedMatchingPath === $normalizedFilePath;
        }
        // ensure add / suffix to ensure no same prefix directory
        if (\is_dir($normalizedMatchingPath)) {
            $normalizedMatchingPath = \rtrim($normalizedMatchingPath, '/') . '/';
        }
        return \strncmp($normalizedFilePath, $normalizedMatchingPath, \strlen($normalizedMatchingPath)) === 0;
    }
}
