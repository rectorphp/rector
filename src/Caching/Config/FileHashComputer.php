<?php

declare (strict_types=1);
namespace Rector\Caching\Config;

use Rector\Application\VersionResolver;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Exception\ShouldNotHappenException;
use Rector\FileSystem\FilePathHelper;
/**
 * Inspired by https://github.com/symplify/easy-coding-standard/blob/e598ab54686e416788f28fcfe007fd08e0f371d9/packages/changed-files-detector/src/FileHashComputer.php
 */
final class FileHashComputer
{
    /**
     * @readonly
     */
    private FilePathHelper $filePathHelper;
    public function __construct(FilePathHelper $filePathHelper)
    {
        $this->filePathHelper = $filePathHelper;
    }
    public function compute(string $filePath): string
    {
        $this->ensureIsPhp($filePath);
        $parametersHash = SimpleParameterProvider::hashForCacheInvalidation();
        // relative config path, so the hash (and the whole cache) is not tied to one directory
        $relativeFilePath = $this->filePathHelper->relativePath($this->filePathHelper->resolveRealPath($filePath));
        return sha1($relativeFilePath . $parametersHash . VersionResolver::PACKAGE_VERSION);
    }
    private function ensureIsPhp(string $filePath): void
    {
        $fileExtension = pathinfo($filePath, \PATHINFO_EXTENSION);
        if ($fileExtension === 'php') {
            return;
        }
        throw new ShouldNotHappenException(sprintf(
            // getRealPath() cannot be used, as it breaks in phar
            'Provide only PHP file, ready for Dependency Injection. "%s" given',
            $filePath
        ));
    }
}
