<?php

if (! interface_exists('UnitEnum', false)) {
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
}

if (! interface_exists('BackedEnum', false)) {
    /**
     * @since 8.1
     */
    interface BackedEnum extends UnitEnum {
        /**
         * @param int|string $value
         * @return $this
         */
        public static function from($value);

        /**
         * @param int|string $value
         * @return $this|null
         */
        public static function tryFrom($value);
    }
}
