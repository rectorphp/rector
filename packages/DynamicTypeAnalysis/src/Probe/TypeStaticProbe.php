<?php

declare(strict_types=1);

namespace Rector\DynamicTypeAnalysis\Probe;

use Nette\Utils\FileSystem;

/**
 * @see https://stackoverflow.com/a/39525458/1348344
 */
final class TypeStaticProbe
{
    /**
     * @var mixed[]
     */
    private static $itemDataByMethodReferenceAndPosition = [];

    public static function recordArgumentType($value, string $method, int $argumentPosition): void
    {
        $probeItem = self::createProbeItem($value, $method, $argumentPosition);

        self::recordProbeItem($probeItem);
    }

    public static function createProbeItem($value, string $method, int $argumentPosition): string
    {
        $type = self::resolveValueTypeToString($value);
        $data = [$type, $method, $argumentPosition];

        return implode(';', $data) . PHP_EOL;
    }

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

    private static function recordProbeItem(string $probeItem): void
    {
        $storageFile = ProbeStaticStorage::getFile();

        if (file_exists($storageFile)) {
            // append
            file_put_contents($storageFile, $probeItem, FILE_APPEND);
        } else {
            // 1st write
            FileSystem::write($storageFile, $probeItem);
        }
    }

    /**
     * @return mixed[]
     */
    private static function provideItemDataByMethodReferenceAndPosition(): array
    {
        if (self::$itemDataByMethodReferenceAndPosition !== []) {
            return self::$itemDataByMethodReferenceAndPosition;
        }

        $probeFileContent = FileSystem::read(ProbeStaticStorage::getFile());

        $probeItems = explode(PHP_EOL, $probeFileContent);
        // remove empty values
        $probeItems = array_filter($probeItems);

        $itemData = [];
        foreach ($probeItems as $probeItem) {
            [$type, $methodReference, $position] = explode(';', $probeItem);

            $itemData[$methodReference][$position][] = $type;
        }

        self::$itemDataByMethodReferenceAndPosition = $itemData;

        return $itemData;
    }
}
