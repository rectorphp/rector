<?php

declare(strict_types=1);

namespace Rector\DynamicTypeAnalysis\Tests\ProbeStorage;

use Rector\DynamicTypeAnalysis\Contract\ProbeStorageInterface;

final class StaticInMemoryProbeStorage implements ProbeStorageInterface
{
    /**
     * @var string[]
     */
    private static $probeItems = [];

    public static function recordProbeItem(string $probeItem): void
    {
        self::$probeItems[] = $probeItem;
    }

    public static function getProbeItems(): array
    {
        // remove empty values
        return array_filter(self::$probeItems);
    }

    public static function clear(): void
    {
        self::$probeItems = [];
    }
}
