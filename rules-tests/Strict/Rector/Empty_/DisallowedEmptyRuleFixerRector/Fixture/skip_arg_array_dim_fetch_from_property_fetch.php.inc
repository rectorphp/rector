<?php

namespace Rector\Tests\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector\Fixture;

class SkipArgArrayDimFetchFromPropertyFetch {
    /**
     * @var array<string>
     */
    protected array $labels = [];

    public function getLabel(string $value): string
    {
        if (empty($this->labels[$value])) {
            throw new \InvalidArgumentException(sprintf('%s is missing label for value "%s"', static::class, $value));
        }

        return $this->labels[$value];
    }
}
