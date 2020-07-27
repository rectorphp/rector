<?php

declare(strict_types=1);

namespace Rector\DynamicTypeAnalysis\Probe;

use Rector\DynamicTypeAnalysis\Contract\ProbeStorageInterface;
use Rector\DynamicTypeAnalysis\ProbeStorage\StaticFileSystemProbeStorage;

/**
 * @see https://stackoverflow.com/a/39525458/1348344
 */
final class TypeStaticProbe
{
    /**
     * @var mixed[]
     */
    private static $itemDataByMethodReferenceAndPosition = [];

    /**
     * @var ProbeStorageInterface|null
     */
    private static $probeStorage;

    /**
     * @param mixed $value
     */
    public static function recordArgumentType($value, string $method, int $argumentPosition): void
    {
        $probeItem = self::createProbeItem($value, $method, $argumentPosition);

        self::recordProbeItem($probeItem);
    }

    /**
     * @param mixed $value
     */
    public static function createProbeItem($value, string $method, int $argumentPosition): string
    {
        $type = self::resolveValueTypeToString($value);
        $data = [$type, $method, $argumentPosition];

        return implode(';', $data) . PHP_EOL;
    }

    /**
     * @param object|mixed[]|mixed $value
     */
    public static function resolveValueTypeToString($value): string
    {
        if (is_object($value)) {
            return 'object:' . get_class($value);
        }

        if (is_array($value)) {
            // try to resolve single nested array types
            $arrayValueTypes = [];
            foreach ($value as $singleValue) {
                $arrayValueTypes[] = self::resolveValueTypeToString($singleValue);
            }

            $arrayValueTypes = array_unique($arrayValueTypes);
            $arrayValueTypes = implode('|', $arrayValueTypes);

            return 'array:' . $arrayValueTypes;
        }

        return gettype($value);
    }

    /**
     * @return mixed[]
     */
    public static function getDataForMethodByPosition(string $methodReference): array
    {
        $probeItemData = self::provideItemDataByMethodReferenceAndPosition();

        return $probeItemData[$methodReference] ?? [];
    }

    public static function setProbeStorage(ProbeStorageInterface $probeStorage): void
    {
        self::$probeStorage = $probeStorage;
    }

    private static function recordProbeItem(string $probeItem): void
    {
        self::getProbeStorage()::recordProbeItem($probeItem);
    }

    /**
     * @return mixed[]
     */
    private static function provideItemDataByMethodReferenceAndPosition(): array
    {
        if (self::$itemDataByMethodReferenceAndPosition !== []) {
            return self::$itemDataByMethodReferenceAndPosition;
        }

        $probeItems = self::getProbeStorage()::getProbeItems();

        $itemData = [];
        foreach ($probeItems as $probeItem) {
            $probeItem = trim($probeItem);
            [$type, $methodReference, $position] = explode(';', $probeItem);

            $itemData[$methodReference][$position][] = $type;
        }

        self::$itemDataByMethodReferenceAndPosition = $itemData;

        return $itemData;
    }

    private static function getProbeStorage(): ProbeStorageInterface
    {
        if (self::$probeStorage === null) {
            // set default filesystem storage
            self::$probeStorage = new StaticFileSystemProbeStorage();
        }

        return self::$probeStorage;
    }
}
