<?php

declare (strict_types=1);
namespace Rector\FileSystem;

/**
 * @see \Rector\Tests\FileSystem\FileAndDirectoryFilter\FileAndDirectoryFilterTest
 */
final class FileAndDirectoryFilter
{
    /**
     * @param string[] $filesAndDirectories
     * @return string[]
     */
    public function filterDirectories(array $filesAndDirectories): array
    {
        $directories = array_filter($filesAndDirectories, \Closure::fromCallable('is_dir'));
        return array_values($directories);
    }
    /**
     * @param string[] $filesAndDirectories
     * @return string[]
     */
    public function filterFiles(array $filesAndDirectories): array
    {
        $files = array_filter($filesAndDirectories, \Closure::fromCallable('is_file'));
        return array_values($files);
    }
}
