<?php

declare(strict_types=1);

namespace Rector\Core\FileSystem;

use Nette\Utils\Strings;

final class FilesystemTweaker
{
    /**
     * This will turn paths like "src/Symfony/Component/*\/Tests" to existing directory paths
     *
     * @param string[] $paths
     *
     * @return string[]
     */
    public function resolveWithFnmatch(array $paths): array
    {
        $absolutePathsFound = [];
        foreach ($paths as $path) {
            if (Strings::contains($path, '*')) {
                $foundPaths = $this->foundInGlob($path);
                $absolutePathsFound = array_merge($absolutePathsFound, $foundPaths);
            } else {
                $absolutePathsFound[] = $path;
            }
        }

        return $absolutePathsFound;
    }

    /**
     * @return string[]
     */
    private function foundInGlob(string $path): array
    {
        $foundPaths = [];

        foreach ((array) glob($path) as $foundPath) {
            if (! is_string($foundPath)) {
                continue;
            }

            if (! file_exists($foundPath)) {
                continue;
            }

            $foundPaths[] = $foundPath;
        }

        return $foundPaths;
    }
}
