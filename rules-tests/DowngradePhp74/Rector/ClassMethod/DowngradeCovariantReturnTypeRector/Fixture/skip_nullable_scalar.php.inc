<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

class NullableScalarA
{
    public function covariantReturnTypes(): ?string
    {
    }
}

class NullableScalarB extends NullableScalarA
{
    public function covariantReturnTypes(): ?string
    {
    }
}
