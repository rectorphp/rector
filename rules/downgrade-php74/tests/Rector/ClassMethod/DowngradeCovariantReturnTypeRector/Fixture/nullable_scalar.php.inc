<?php

namespace Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

class NullableScalarParentType {}
class NullableScalarChildType extends NullableScalarParentType {}

class NullableScalarA
{
    public function covariantReturnTypes(): ?string
    { /* … */ }
}

class NullableScalarB extends NullableScalarA
{
    public function covariantReturnTypes(): ?string
    { /* … */ }
}

?>
