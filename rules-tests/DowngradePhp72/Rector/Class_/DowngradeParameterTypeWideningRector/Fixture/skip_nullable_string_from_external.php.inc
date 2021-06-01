<?php

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

use Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Source\ParentNullableString;

final class SkipNullableStringFromExternal extends ParentNullableString
{
    public function load(string $value = null)
    {
    }
}

?>