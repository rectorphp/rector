<?php

namespace Rector\Tests\Php81\Rector\Property\ReadOnlyPropertyRector\Fixture;

final class SkipUsedAsArrayDimFetchInSideEffectCallArg
{
    /**
     * @param array<string, array<string>> $items
     */
    public function __construct(private array $items)
    {}

    public function popFooItem(): void
    {
        if (!empty($this->items) && array_key_exists('foo', $this->items)) {
            array_pop($this->items['foo']);
        }
    }
}
