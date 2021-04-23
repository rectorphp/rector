<?php

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

interface KeepOriginalIfSharedType
{
    public function test(array $input);
}

final class UpdateBoth implements KeepOriginalIfSharedType
{
    public function test(array $input)
    {
    }
}
