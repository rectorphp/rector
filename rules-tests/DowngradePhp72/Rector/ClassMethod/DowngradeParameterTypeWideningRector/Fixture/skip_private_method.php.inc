<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

class SkipPrivateMethod
{
    private function has(string $callable)
    {
    }
}
