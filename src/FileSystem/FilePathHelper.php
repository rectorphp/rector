<?php

declare (strict_types=1);
namespace Rector\Core\FileSystem;

use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileSystem;
final class FilePathHelper
{
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    public function __construct(SmartFileSystem $smartFileSystem)
    {
        $this->smartFileSystem = $smartFileSystem;
    }
    public function relativePath(string $fileRealPath) : string
    {
        $normalizedFileRealPath = $this->normalizePath($fileRealPath);
        $relativeFilePath = $this->smartFileSystem->makePathRelative($normalizedFileRealPath, (string) \realpath(\getcwd()));
        return \rtrim($relativeFilePath, '/');
    }
    private function normalizePath(string $filePath) : string
    {
        return \str_replace('\\', '/', $filePath);
    }
}
