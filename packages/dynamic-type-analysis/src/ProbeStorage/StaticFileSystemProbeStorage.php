<?php

declare(strict_types=1);

namespace Rector\DynamicTypeAnalysis\ProbeStorage;

use Rector\DynamicTypeAnalysis\Contract\ProbeStorageInterface;
use Symplify\SmartFileSystem\SmartFileSystem;

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
            $smartFileSystem = new SmartFileSystem();
            $smartFileSystem->dumpFile($storageFile, $probeItem);
        }
    }

    public static function clear(): void
    {
        $smartFileSystem = new SmartFileSystem();
        $smartFileSystem->remove(self::getFile());
    }

    /**
     * @return string[]
     */
    public static function getProbeItems(): array
    {
        $smartFileSystem = new SmartFileSystem();
        $probeFileContent = $smartFileSystem->readFile(self::getFile());

        $probeItems = explode(PHP_EOL, $probeFileContent);

        // remove empty values
        return array_filter($probeItems);
    }

    private static function getFile(): string
    {
        return sys_get_temp_dir() . '/_rector_type_probe.txt';
    }
}
