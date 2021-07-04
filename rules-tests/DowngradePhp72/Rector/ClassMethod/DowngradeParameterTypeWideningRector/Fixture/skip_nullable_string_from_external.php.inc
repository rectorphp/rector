<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

use Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Source\ParentNullableString;

final class SkipNullableStringFromExternal extends ParentNullableString
{
    public function load(string $value = null)
    {
    }
}

?>
