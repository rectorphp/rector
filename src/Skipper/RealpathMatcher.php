<?php

declare (strict_types=1);
namespace Rector\Skipper;

use Rector\Skipper\FileSystem\PathNormalizer;
final class RealpathMatcher
{
    public function match(string $matchingPath, string $filePath) : bool
    {
        $realPathMatchingPath = \realpath($matchingPath);
        if ($realPathMatchingPath === \false) {
            return \false;
        }
        $realpathFilePath = \realpath($filePath);
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
        $suffixedMatchingPath = \rtrim($normalizedMatchingPath, '/') . '/';
        return \strncmp($normalizedFilePath, $suffixedMatchingPath, \strlen($suffixedMatchingPath)) === 0;
    }
}
