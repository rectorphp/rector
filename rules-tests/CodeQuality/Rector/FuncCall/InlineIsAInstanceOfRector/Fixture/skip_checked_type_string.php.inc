<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\InlineIsAInstanceOfRector\Fixture;

use Rector\Tests\CodeQuality\Rector\FuncCall\InlineIsAInstanceOfRector\Source\SomeType;

final class SkipCheckedTypeString
{
    public function run(string $stringType)
    {
        return is_a($stringType, SomeType::class);
    }
}
