<?php

namespace RectorPrefix202402\Illuminate\Contracts\Support;

/**
 * @template TKey of array-key
 * @template TValue
 */
interface Arrayable
{
    /**
     * Get the instance as an array.
     *
     * @return array<TKey, TValue>
     */
    public function toArray();
}
