<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

use stdClass;

final class SkipHasReturnReassignWhenEmpty
{
    private array $data = [];

    public function init(string $key, stdClass $stdClass): void
    {
        $this->data[$key] = $stdClass;
    }

    public function execute(string $key)
    {
        return $this->data[$key] ??= new stdClass();
    }
}
