<?php

declare(strict_types=1);

namespace Rector\Tests\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture
{
    final class FqnClassReference
    {
        public function handle(): void {}
    }
}

namespace {
    doFoo([\Rector\Tests\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture\FqnClassReference::class, 'handle']);

    function doFoo($method): void
    {
    }
}
