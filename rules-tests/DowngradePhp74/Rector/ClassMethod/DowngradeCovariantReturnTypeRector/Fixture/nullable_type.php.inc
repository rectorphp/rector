<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

class NullableParentType {}
class NullableChildType extends NullableParentType {}

class NullableA
{
    public function covariantReturnTypes(): ?NullableParentType
    { /* … */ }
}

class NullableB extends NullableA
{
    public function covariantReturnTypes(): ?NullableChildType
    { /* … */ }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture;

class NullableParentType {}
class NullableChildType extends NullableParentType {}

class NullableA
{
    public function covariantReturnTypes(): ?NullableParentType
    { /* … */ }
}

class NullableB extends NullableA
{
    public function covariantReturnTypes(): ?\Rector\Tests\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector\Fixture\NullableParentType
    { /* … */ }
}

?>
