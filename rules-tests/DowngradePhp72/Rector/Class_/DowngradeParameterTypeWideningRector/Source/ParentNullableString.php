<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Source;

class ParentNullableString
{
    public function load(string $value = null)
    {
    }
}
