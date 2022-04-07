<?php

namespace Rector\Tests\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector\Fixture;

class SkipArgArrayDimFetchFromStaticPropertyFetch {
    /**
     * @var array<string>
     */
    protected static array $labels = [];

    public static function getLabel(string $value): string
    {
        if (empty(static::$labels[$value])) {
            throw new \InvalidArgumentException(sprintf('%s is missing label for value "%s"', static::class, $value));
        }

        return static::$labels[$value];
    }
}
