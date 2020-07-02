<?php

declare(strict_types=1);

namespace Rector\DynamicTypeAnalysis\Contract;

interface ProbeStorageInterface
{
    public static function recordProbeItem(string $probeItem): void;

    /**
     * @return string[]
     */
    public static function getProbeItems(): array;

    public static function clear(): void;
}
