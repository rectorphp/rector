<?php

declare(strict_types=1);

namespace Rector\DoctrineAnnotationGenerated\DataCollector;

final class ResolvedConstantStaticCollector
{
    /**
     * @var mixed[]
     */
    private static $valuesByIdentifier = [];

    public static function collect(string $identifier, $value): void
    {
        // skip PHP values
        $lowercasedIdentifier = strtolower($identifier);
        if (in_array($lowercasedIdentifier, ['true', 'false', 'null'], true)) {
            return;
        }

        self::$valuesByIdentifier[$identifier] = $value;
    }

    /**
     * @return mixed[]
     */
    public static function provide(): array
    {
        return self::$valuesByIdentifier;
    }

    public static function clear(): void
    {
        self::$valuesByIdentifier = [];
    }
}
