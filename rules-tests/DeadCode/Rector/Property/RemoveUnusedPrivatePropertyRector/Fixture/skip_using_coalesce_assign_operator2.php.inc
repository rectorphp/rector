<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

final class SkipUsingCoalesceAssignOperator2
{
    private SomeService $someService;

    private array $types = [];

    public function __construct(SomeService $someService)
    {
        $this->someService = $someService;
    }

    public function get(string $key, string $key2)
    {
        return $this->types[$key][$key2] ??= $this->someService->resolve();
    }
}
