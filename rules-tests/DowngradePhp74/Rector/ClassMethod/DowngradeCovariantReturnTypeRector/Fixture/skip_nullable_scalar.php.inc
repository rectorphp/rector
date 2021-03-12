<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

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
