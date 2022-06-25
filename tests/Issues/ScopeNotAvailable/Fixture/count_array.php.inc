<?php

namespace Rector\Core\Tests\Issues\ScopeNotAvailable\Fixture;

class CountArray
{
    public function decode(string $hash): array
    {
        return [];
    }

    public function decodeOne(string $hash): ?int
    {
        $values = $this->decode($hash);
        if (count($values) === 0) {
            return null;
        }

        return $values[0];
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\ScopeNotAvailable\Fixture;

class CountArray
{
    public function decode(string $hash): array
    {
        return [];
    }

    public function decodeOne(string $hash): ?int
    {
        $values = $this->decode($hash);
        if ($values === []) {
            return null;
        }

        return $values[0];
    }
}

?>
