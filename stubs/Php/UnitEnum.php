<?php

declare(strict_types=1);

if (interface_exists('UnitEnum')) {
    return;
}

/**
 * @since 8.1
 */
interface UnitEnum
{
    /**
     * @return static[]
     */
    public static function cases(): array;
}
