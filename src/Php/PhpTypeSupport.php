<?php declare(strict_types=1);

namespace Rector\Php;

/**
 * Static service to work with type support:
 *
 * - PHP 7.2: object
 */
final class PhpTypeSupport
{
    /**
     * @var string[]
     */
    private static $types = [];

    public static function enableType(string $name): void
    {
        self::$types[] = $name;
    }

    public static function isTypeSupported(string $name): bool
    {
        return in_array($name, self::$types, true);
    }

    public static function disableType(string $name): void
    {
        self::$types = array_diff(self::$types, [$name]);
    }
}
