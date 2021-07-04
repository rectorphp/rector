<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

use Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Source\vendor\NullableStringTrait;

final class SkipNullableStringFromExternalTrait
{
    use NullableStringTrait;

    public function load(string $value = null)
    {
    }
}
