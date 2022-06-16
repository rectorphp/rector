<?php

class Preg
{
    /**
     * @param string $subject
     */
    public static function replaceCallbackArray(array $pattern, $subject, int $limit = -1, int &$count = null, int $flags = 0): string
    {
        if (!is_scalar($subject)) {
            if (is_array($subject)) {
                throw new \InvalidArgumentException(static::ARRAY_MSG);
            }

            throw new \TypeError(sprintf(static::INVALID_TYPE_MSG, gettype($subject)));
        }
    }
}
