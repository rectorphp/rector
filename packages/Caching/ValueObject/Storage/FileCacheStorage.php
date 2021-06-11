<?php

declare(strict_types=1);

namespace Rector\Caching\ValueObject\Storage;

use Nette\Utils\Random;
use Nette\Utils\Strings;
use Rector\Caching\ValueObject\CacheFilePaths;
use Rector\Caching\ValueObject\CacheItem;
use Symplify\EasyCodingStandard\Caching\Exception\CachingException;
use Symplify\SmartFileSystem\SmartFileSystem;

/**
 * Inspired by
 * https://github.com/phpstan/phpstan-src/commit/4df7342f3a0aaef4bcd85456dd20ca88d38dd90d#diff-6dc14f6222bf150e6840ca44a7126653052a1cedc6a149b4e5c1e1a2c80eacdc
 */
final class FileCacheStorage
{
    public function __construct(
        private string $directory,
        private SmartFileSystem $smartFileSystem
    ) {
    }

    /**
     * @return mixed|null
     */
    public function load(string $key, string $variableKey)
    {
        $cacheFilePaths = $this->getCacheFilePaths($key);

        $filePath = $cacheFilePaths->getFilePath();
        if (! is_file($filePath)) {
            return null;
        }

        $cacheItem = require $filePath;
        if (! $cacheItem instanceof CacheItem) {
            return null;
        }

        if (! $cacheItem->isVariableKeyValid($variableKey)) {
            return null;
        }

        return $cacheItem->getData();
    }

    /**
     * @param mixed $data
     */
    public function save(string $key, string $variableKey, $data): void
    {
        $cacheFilePaths = $this->getCacheFilePaths($key);

        $this->smartFileSystem->mkdir($cacheFilePaths->getFirstDirectory());
        $this->smartFileSystem->mkdir($cacheFilePaths->getSecondDirectory());

        $tmpPath = sprintf('%s/%s.tmp', $this->directory, Random::generate());
        $errorBefore = error_get_last();
        $exported = @var_export(new CacheItem($variableKey, $data), true);
        $errorAfter = error_get_last();

        if ($errorAfter !== null && $errorBefore !== $errorAfter) {
            $errorMessage = sprintf(
                'Error occurred while saving item "%s" ("%s") to cache: "%s"',
                $key,
                $variableKey,
                $errorAfter['message']
            );
            throw new CachingException($errorMessage);
        }

        $variableFileContent = sprintf("<?php declare(strict_types = 1);\n\nreturn %s;", $exported);
        $this->smartFileSystem->dumpFile($tmpPath, $variableFileContent);

        $this->smartFileSystem->rename($tmpPath, $cacheFilePaths->getFilePath(), true);
        $this->smartFileSystem->remove($tmpPath);
    }

    public function clean(string $cacheKey): void
    {
        $cacheFilePaths = $this->getCacheFilePaths($cacheKey);

        $this->smartFileSystem->remove([
            $cacheFilePaths->getFirstDirectory(),
            $cacheFilePaths->getSecondDirectory(),
            $cacheFilePaths->getFilePath(),
        ]);
    }

    public function clear(): void
    {
        $this->smartFileSystem->remove($this->directory);
    }

    private function getCacheFilePaths(string $key): CacheFilePaths
    {
        $keyHash = sha1($key);
        $firstDirectory = sprintf('%s/%s', $this->directory, Strings::substring($keyHash, 0, 2));
        $secondDirectory = sprintf('%s/%s', $firstDirectory, Strings::substring($keyHash, 2, 2));
        $filePath = sprintf('%s/%s.php', $secondDirectory, $keyHash);

        return new CacheFilePaths($firstDirectory, $secondDirectory, $filePath);
    }
}
