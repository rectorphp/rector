<?php

namespace Rector\Tests\Php81\Rector\Property\ReadOnlyPropertyRector\Fixture;

class RedisSet
{
    public function __construct(
        private \Redis $redis,
        private int $ttl
    ) {
    }

    public function save(): void
    {
        $this->redis->set(
            'key',
            'value',
            $this->ttl
        );
    }
}

?>
-----
<?php

namespace Rector\Tests\Php81\Rector\Property\ReadOnlyPropertyRector\Fixture;

class RedisSet
{
    public function __construct(
        private readonly \Redis $redis,
        private readonly int $ttl
    ) {
    }

    public function save(): void
    {
        $this->redis->set(
            'key',
            'value',
            $this->ttl
        );
    }
}

?>
