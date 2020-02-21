<?php

namespace Rector\CodeQuality\Tests\Rector\If_\ExplicitBoolCompareRector\Fixture;

final class SkipNestedArrayMixed
{
    public function provider(): array
    {
        return [];
    }

    public function run(): void
    {
        $data = $this->provider();
        $this->value = $data['key'] ? 1 : 2;
    }
}
