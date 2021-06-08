<?php

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

use Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Source\NullableStringTrait;

final class SkipNullableStringFromExternalTrait
{
    use NullableStringTrait;

    public function load(string $value = null)
    {
    }
}

?>