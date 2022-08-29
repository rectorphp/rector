<?php

declare (strict_types=1);
namespace Rector\Core\FileSystem;

use RectorPrefix202208\Symfony\Component\Filesystem\Filesystem;
final class FilePathHelper
{
    /**
     * @readonly
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }
    public function relativePath(string $fileRealPath) : string
    {
        $normalizedFileRealPath = $this->normalizePath($fileRealPath);
        $relativeFilePath = $this->filesystem->makePathRelative($normalizedFileRealPath, (string) \realpath(\getcwd()));
        return \rtrim($relativeFilePath, '/');
    }
    private function normalizePath(string $filePath) : string
    {
        return \str_replace('\\', '/', $filePath);
    }
}
