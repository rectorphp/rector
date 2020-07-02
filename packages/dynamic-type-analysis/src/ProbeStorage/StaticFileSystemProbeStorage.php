<?php

declare(strict_types=1);

namespace Rector\DynamicTypeAnalysis\ProbeStorage;

use Nette\Utils\FileSystem;
use Rector\DynamicTypeAnalysis\Contract\ProbeStorageInterface;

final class StaticFileSystemProbeStorage implements ProbeStorageInterface
{
    public static function recordProbeItem(string $probeItem): void
    {
        $storageFile = self::getFile();
        if (file_exists($storageFile)) {
            // append
            file_put_contents($storageFile, $probeItem, FILE_APPEND);
        } else {
            // 1st write
            FileSystem::write($storageFile, $probeItem);
        }
    }

    public static function clear(): void
    {
        FileSystem::delete(self::getFile());
    }

    /**
     * @return string[]
     */
    public static function getProbeItems(): array
    {
        $probeFileContent = FileSystem::read(self::getFile());

        $probeItems = explode(PHP_EOL, $probeFileContent);

        // remove empty values
        return array_filter($probeItems);
    }

    private static function getFile(): string
    {
        return sys_get_temp_dir() . '/_rector_type_probe.txt';
    }
}
