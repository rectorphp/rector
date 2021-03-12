<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

final class SkipReadDimFetchUse
{
    /**
     * @var string
     */
    private $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function buildData(): array
    {
        $data[$this->key] = 100000000;

        return $data;
    }
}
