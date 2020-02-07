<?php

declare(strict_types=1);

namespace Rector\DynamicTypeAnalysis\Probe;

use Nette\Utils\FileSystem;

final class ProbeStaticStorage
{
    public static function getFile(): string
    {
        return sys_get_temp_dir() . '/_rector_type_probe.txt';
    }

    public static function clear(): void
    {
        FileSystem::delete(self::getFile());
    }
}
