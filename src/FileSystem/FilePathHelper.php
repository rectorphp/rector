<?php

declare (strict_types=1);
namespace Rector\Core\FileSystem;

use RectorPrefix202209\Symfony\Component\Filesystem\Filesystem;
use RectorPrefix202209\Webmozart\Assert\Assert;
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
        if (!$this->filesystem->isAbsolutePath($fileRealPath)) {
            return $fileRealPath;
        }
        return $this->relativeFilePathFromDirectory($fileRealPath, \getcwd());
    }
    /**
     * @api
     */
    public function relativeFilePathFromDirectory(string $fileRealPath, string $directory) : string
    {
        Assert::directory($directory);
        $normalizedFileRealPath = $this->normalizePath($fileRealPath);
        $relativeFilePath = $this->filesystem->makePathRelative($normalizedFileRealPath, $directory);
        return \rtrim($relativeFilePath, '/');
    }
    private function normalizePath(string $filePath) : string
    {
        return \str_replace('\\', '/', $filePath);
    }
}
