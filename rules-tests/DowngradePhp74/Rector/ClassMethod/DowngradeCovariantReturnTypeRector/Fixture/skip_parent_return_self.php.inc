<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

interface AnInterface
{
    public function run(): self;
}

class AClass implements AnInterface
{
    public function run(): AnInterface
    {
        return $this;
    }
}
