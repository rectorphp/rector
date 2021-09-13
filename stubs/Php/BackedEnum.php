<?php

declare(strict_types=1);

if (interface_exists('BackedEnum')) {
    return;
}

/**
 * @since 8.1
 */
interface BackedEnum extends UnitEnum {
    public static function from(int|string $value): static;
    public static function tryFrom(int|string $value): ?static;
}
