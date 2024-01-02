<?php

declare (strict_types=1);
namespace Rector\FileSystem;

final class FilesystemTweaker
{
    /**
     * This will turn paths like "src/Symfony/Component/*\/Tests" to existing directory paths
     *
     * @param string[] $paths
     *
     * @return string[]
     */
    public function resolveWithFnmatch(array $paths) : array
    {
        $absolutePathsFound = [];
        foreach ($paths as $path) {
            if (\strpos($path, '*') !== \false) {
                $foundPaths = $this->foundInGlob($path);
                $absolutePathsFound = \array_merge($absolutePathsFound, $foundPaths);
            } else {
                $absolutePathsFound[] = $path;
            }
        }
        return $absolutePathsFound;
    }
    /**
     * @return string[]
     */
    private function foundInGlob(string $path) : array
    {
        /** @var string[] $paths */
        $paths = (array) \glob($path);
        return \array_filter($paths, static function (string $path) : bool {
            return \file_exists($path);
        });
    }
}
