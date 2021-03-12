<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

final class SkipDimFetchUsePropertyFetch
{
    /**
     * @var string
     */
    private $key;

    protected $writeOnly;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function buildData(): array
    {
        $this->writeOnly[$this->key] = 10000;

        return $this->writeOnly;
    }
}
