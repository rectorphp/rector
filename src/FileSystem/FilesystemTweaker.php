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
                $absolutePathsFound = $this->appendPaths($foundPaths, $absolutePathsFound);
            } else {
                $absolutePathsFound = $this->appendPaths([$path], $absolutePathsFound);
            }
        }
        return $absolutePathsFound;
    }
    /**
     * @param string[] $foundPaths
     * @param string[] $absolutePathsFound
     * @return string[]
     */
    private function appendPaths(array $foundPaths, array $absolutePathsFound) : array
    {
        foreach ($foundPaths as $foundPath) {
            $foundPath = \realpath($foundPath);
            if ($foundPath === \false) {
                continue;
            }
            $absolutePathsFound[] = $foundPath;
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
        return \array_filter($paths, static fn(string $path): bool => \file_exists($path));
    }
}
