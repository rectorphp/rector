<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

final class SkipUsingCoalesceAssignOperator
{
    private SomeService $someService;

    private array $types = [];

    public function __construct(SomeService $someService)
    {
        $this->someService = $someService;
    }

    public function get(string $key)
    {
        return $this->types[$key] ??= $this->someService->resolve();
    }
}
