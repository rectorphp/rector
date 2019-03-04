<?php declare(strict_types=1);

namespace Rector\Laravel\Tests\Rector\StaticCall\MinutesToSecondsInCacheRector\Source;

final class ArrayStore implements LaravelStoreInterface
{
    public function get($key)
    {
    }

    public function many(array $keys)
    {
    }

    public function put($key, $value, $seconds)
    {
    }

    public function putMany(array $values, $seconds)
    {
    }

    public function increment($key, $value = 1)
    {
    }

    public function decrement($key, $value = 1)
    {
    }

    public function forever($key, $value)
    {
    }

    public function forget($key)
    {
    }

    public function flush()
    {
    }

    public function getPrefix()
    {
    }
}
