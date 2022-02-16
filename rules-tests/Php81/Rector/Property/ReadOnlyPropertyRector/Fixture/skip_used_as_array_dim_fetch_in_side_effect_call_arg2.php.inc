<?php

namespace Rector\Tests\Php81\Rector\Property\ReadOnlyPropertyRector\Fixture;

final class SkipUsedAsArrayDimFetchInSideEffectCallArg2
{
    private array $items;

    /**
     * @param array<string, array<string>> $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function popFooItem(): void
    {
        if (!empty($this->items) && array_key_exists('foo', $this->items)) {
            array_pop($this->items['foo']);
        }
    }
}
