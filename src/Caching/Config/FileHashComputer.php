<?php

declare (strict_types=1);
namespace Rector\Caching\Config;

use Rector\Application\VersionResolver;
use Rector\Caching\Contract\CacheMetaExtensionInterface;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Exception\ShouldNotHappenException;
/**
 * Inspired by https://github.com/symplify/easy-coding-standard/blob/e598ab54686e416788f28fcfe007fd08e0f371d9/packages/changed-files-detector/src/FileHashComputer.php
 */
final class FileHashComputer
{
    /**
     * @var CacheMetaExtensionInterface[]
     * @readonly
     */
    private array $cacheMetaExtensions = [];
    /**
     * @param CacheMetaExtensionInterface[] $cacheMetaExtensions
     */
    public function __construct(array $cacheMetaExtensions = [])
    {
        $this->cacheMetaExtensions = $cacheMetaExtensions;
    }
    public function compute(string $filePath): string
    {
        $this->ensureIsPhp($filePath);
        $parametersHash = SimpleParameterProvider::hash();
        $extensionHash = $this->computeExtensionHash();
        return sha1($filePath . $parametersHash . $extensionHash . VersionResolver::PACKAGE_VERSION);
    }
    private function computeExtensionHash(): string
    {
        $extensionHash = '';
        foreach ($this->cacheMetaExtensions as $cacheMetumExtension) {
            $extensionHash .= $cacheMetumExtension->getKey() . ':' . $cacheMetumExtension->getHash();
        }
        return $extensionHash;
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
