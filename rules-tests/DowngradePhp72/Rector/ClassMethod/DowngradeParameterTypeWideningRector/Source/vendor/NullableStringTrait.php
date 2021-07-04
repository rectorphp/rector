<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Source\vendor;

trait NullableStringTrait
{
    public function load(string $value = null)
    {
    }
}
